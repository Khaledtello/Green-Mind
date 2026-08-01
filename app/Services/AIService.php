<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
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
        try {
            $response = Http::attach(
                'file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )->post("{$this->pythonBaseUrl}/predict");

            if ($response->failed())
                throw new \Exception(__('api.ai_connection_failed') . $response->body());

            return $response->json();
        } catch (ConnectionException $e) {
            $imageData = file_get_contents($image->getRealPath());
            $base64Image = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            return [
                'disease_name_technical' => 'Tomato_Late_blight',
                'disease_name_arabic' => 'لفحة البندورة المتأخرة (بيانات تجريبية)',
                'confidence' => 95.50,
                'grad_cam_base64' => $base64Image,
                'treatment' => 'هذا علاج تجريبي لكي يتمكن مطور الواجهة الأمامية من بناء الواجهة دون الحاجة لخادم الذكاء الاصطناعي.'
            ];
        }
    }
}
