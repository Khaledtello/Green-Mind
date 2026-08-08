<?php

namespace App\Services;

use Exception;
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
            $response = Http::attach(
                'file',
                file_get_contents($image->getRealPath()),
                $image->getClientOriginalName()
            )->timeout(180)->post("{$this->pythonUrl}/predict?include_llm=false");

            if ($response->failed())
                throw new \Exception(__('api.ai_connection_failed') . $response->body());

            return $response->json();
        } catch (ConnectionException $e) {
            $imageData   = file_get_contents($image->getRealPath());
            $base64Image = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            return [
                'disease_name_technical' => 'Test_tomato_Late_blight',
                'disease_name_arabic'    => 'لفحة البندورة المتأخرة (بيانات تجريبية)',
                'confidence'             => 95.50,
                'grad_cam_base64'        => $base64Image,
                'treatment'              => 'هذا علاج تجريبي لكي يتمكن مطور الواجهة الأمامية من بناء الواجهة دون الحاجة لخادم الذكاء الاصطناعي.',
                'schedule_recommendation' => ['recommended_interval_days' => 4],
                'details' => [
                    'local_name'      => 'لفحة البندورة (تجريبي)',
                    'symptoms'        => 'بقع بنية على الأوراق (تجريبي)',
                    'syrian_remedy'   => 'استخدام مبيد فطري (تجريبي)',
                    'organic_advice'  => 'إزالة الأوراق المصابة (تجريبي)',
                    'local_timing'    => 'آذار - تشرين الأول (تجريبي)',
                    'official_source' => 'وزارة الزراعة السورية (تجريبي)'
                ]
            ];
        }
    }

    /**
     * استفسار البوت الخبير الزراعي (RAG)
     */
    public function chat(string $msg, ?string $ctx = null): array
    {
        $res = Http::timeout(180)->post("{$this->pythonUrl}/chat", [
            'message' => $msg,
            'context' => $ctx,
        ]);

        if ($res->failed()) {
            throw new \Exception("فشل الاتصال بـ البوت الخبير الزراعي.");
        }

        return $res->json();
    }

    public function chatStream(string $msg, ?string $ctx = null): \Generator
    {
        try {
            $response = Http::withOptions(['stream' => true])
                ->timeout(180)
                ->post("{$this->pythonUrl}/chat", [
                    'message' => $msg,
                    'context' => $ctx,
                ]);

            if ($response->failed()) {
                yield "عذراً، تعذر الاتصال بخدمة الذكاء الاصطناعي.";
                return;
            }

            $body = $response->getBody();

            while (!$body->eof()) {
                $chunk = $body->read(128);
                if ($chunk !== '')
                    yield $chunk;
            }
        } catch (ConnectionException $e) {
            yield "عذراً، خادم الذكاء الاصطناعي غير متصل حالياً.";
        } catch (Exception $e) {
            yield "حدث خطأ غير متوقع.";
        }
    }

    /**
     * اقتراح تعديل جدول السقاية
     */
    public function proposeSchedule(string $cropType, string $diseaseClass = 'healthy'): array
    {
        $res = Http::post("{$this->pythonUrl}/propose-schedule", [
            'crop_type' => $cropType,
            'disease_name_technical' => $diseaseClass,
        ]);

        if ($res->failed()) {
            throw new \Exception("فشل حساب اقتراح التعديل لجدول الري.");
        }

        return $res->json();
    }
}
