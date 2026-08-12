<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\UploadedFile;

class AIService
{
    private string $pythonUrl;

    public function __construct()
    {
        $this->pythonUrl = env('PYTHON_AI_URL', 'http://127.0.0.1:5000');
    }

    public function diagnose(UploadedFile $image): array
    {
        try {
            $response = Http::timeout(180)
                ->withHeaders(['Accept-Language' => app()->getLocale()])
                ->attach(
                    'file',
                    file_get_contents($image->getRealPath()),
                    $image->getClientOriginalName()
                )
                ->post("{$this->pythonUrl}/predict");

            if ($response->failed())
                throw new \Exception(__('api.ai_connection_failed') . $response->body());

            return $response->json();
        } catch (ConnectionException $e) {
            $imageData   = file_get_contents($image->getRealPath());
            $base64Image = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            return [
                'disease_name_technical'  => 'Test_Tomato_Late_blight',
                'disease_name_arabic'     => 'لفحة البندورة المتأخرة (بيانات تجريبية)',
                'disease_name_english'    => 'Test tomato late blight',
                'confidence'              => 95.50,
                'grad_cam_base64'         => $base64Image,
                'treatment'               => 'هذا علاج تجريبي لكي يتمكن مطور الواجهة الأمامية من بناء الواجهة دون الحاجة لخادم الذكاء الاصطناعي.',
                'schedule_recommendation' => [
                    'recommended_interval_days' => 4,
                    'reason'                    => 'برأي هيك أحسن',
                ],
                'details' => [
                    'local_name'      => 'لفحة البندورة (تجريبي)',
                    'symptoms'        => 'بقع بنية على الأوراق (تجريبي)',
                    'syrian_remedy'   => 'استخدام مبيد فطري (تجريبي)',
                    'organic_advice'  => 'إزالة الأوراق المصابة (تجريبي)',
                    'local_timing'    => 'آذار - تشرين الأول (تجريبي)',
                    'official_source' => 'وزارة الزراعة السورية (تجريبي)'
                ],
                'top_predictions'         => null,
            ];
        }
    }

    public function chatStream(string $msg, ?string $ctx = null): \Generator
    {
        try {
            $response = Http::timeout(180)
                ->withOptions(['stream' => true])
                ->withHeaders([
                    'Accept-Language' => app()->getLocale(),
                    'Accept'          => 'text/event-stream'
                ])
                ->post("{$this->pythonUrl}/chat", [
                    'message' => $msg,
                    'context' => $ctx,
                ]);

            if ($response->failed())
                throw new \Exception(__('api.ai_connection_failed') . $response->body());

            $body = $response->getBody();

            while (!$body->eof()) {
                $chunk = $body->read(128);
                if ($chunk !== '')
                    yield $chunk;
            }
        } catch (ConnectionException $e) {
            $mockText = "هذه إجابة تجريبية من البوت الخبير (لأن خادم الذكاء الاصطناعي غير متصل حالياً). بناءً على سؤالك: '" . $msg . "'، ننصح بمراجعة المختصين الزراعيين واستخدام المبيدات المناسبة المتوفرة في السوق السوري.";
            $words = explode(' ', $mockText);

            foreach ($words as $word) {
                yield $word . ' ';
                usleep(50000);
            }
        }
    }

    public function proposeSchedule(string $cropType, string $diseaseClass = 'healthy'): array
    {
        $response = Http::post("{$this->pythonUrl}/propose-schedule", [
            'crop_type' => $cropType,
            'disease_name_technical' => $diseaseClass,
        ]);

        if ($response->failed())
            throw new \Exception(__('api.ai_connection_failed') . $response->body());

        return $response->json();
    }
}
