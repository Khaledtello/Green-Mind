<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatRequest;
use App\Models\ChatLog;
use App\Services\AIService;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(private AIService $aiService) {}

    /**
     * Handle the chat request with Streaming/JSON response.
     */
    public function chat(ChatRequest $request)
    {
        $user      = $request->user();
        $sessionId = $request['session_id'] ?? Str::uuid()->toString();
        $message   = $request['message'];

        $history = ChatLog::where('session_id', $sessionId)
            ->latest('id')
            ->take(5)
            ->get()
            ->reverse();

        ChatLog::create([
            'user_id'    => $user->id,
            'session_id' => $sessionId,
            'role'       => 'user',
            'message'    => $message,
        ]);

        $context = "";
        foreach ($history as $log)
            $context .= "{$log->role}: {$log->message}\n";

        if ($request['context'])
            $context .= "notes: {$request['context']}\n";

        $isStream = $request->header('Accept') === 'text/event-stream';

        // ------------------ Stream ------------------
        if ($isStream)
            return response()->stream(function () use ($message, $context, $user, $sessionId) {
                $fullResponse = '';

                foreach ($this->aiService->chatStream($message, $context) as $chunk) {
                    $fullResponse .= $chunk;
                    echo $chunk;

                    if (ob_get_level() > 0)
                        ob_flush();

                    flush();
                }

                if (!empty($fullResponse)) {
                    ChatLog::create([
                        'user_id'    => $user->id,
                        'session_id' => $sessionId,
                        'role'       => 'assistant',
                        'message'    => $fullResponse,
                    ]);
                }
            }, 200, [
                'Content-Type'      => 'text/event-stream',
                'Cache-Control'     => 'no-cache',
                'Connection'        => 'keep-alive',
                'X-Accel-Buffering' => 'no',
                'X-Session-Id'      => $sessionId,
            ]);

        // ------------------ JSON ------------------
        $aiResult   = $this->aiService->chat($message, $context);
        $aiResponse = $aiResult['response'];
        $sources    = $aiResult['sources'];

        ChatLog::create([
            'user_id'    => $user->id,
            'session_id' => $sessionId,
            'role'       => 'assistant',
            'message'    => $aiResponse,
            'sources'    => $sources,
        ]);

        return $this->dataResponse([
            'session_id' => $sessionId,
            'reply'      => $aiResponse,
            'sources'    => $sources
        ]);
    }
}
