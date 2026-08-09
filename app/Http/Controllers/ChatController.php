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
     * Handle the chat request with Streaming response.
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

        $historyContext = "";
        foreach ($history as $log)
            $historyContext .= "{$log->role}: {$log->message}\n";

        $diseaseContext = $request['context'] ?? null;
        $finalContext = $historyContext;
        if ($diseaseContext)
            $finalContext .= "\nملاحظة هامة: المستخدم قام للتو بتشخيص نباتته ووجد أنها مصابة بـ ({$diseaseContext}).";

        return response()->stream(function () use ($message, $finalContext, $user, $sessionId) {
            $fullResponse = '';

            foreach ($this->aiService->chatStream($message, $finalContext) as $chunk) {
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
    }
}
