<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class AIService
{
    private string $pythonBaseUrl;

    public function __construct()
    {
        $this->pythonBaseUrl = env('PYTHON_AI_URL', 'http://127.0.0.1:5000');
    }

    public function diagnose(UploadedFile $image): array
    {
        $response = Http::attach(
            'file',
            file_get_contents($image->getRealPath()),
            $image->getClientOriginalName()
        )->post("{$this->pythonBaseUrl}/predict");

        if ($response->failed())
            throw new \Exception(__('api.ai_connection_failed'));

        return $response->json();
    }
}
