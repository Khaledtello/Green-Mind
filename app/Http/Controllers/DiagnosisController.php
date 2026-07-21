<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnosisRequest;
use App\Models\DiagnosisHistory;
use App\Models\Plant;
use App\Services\AIService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiagnosisController extends Controller
{
    public function predict(DiagnosisRequest $request, AIService $aiService)
    {
        try {
            $image = $request->file('image');

            $imageName = time() . '_' . Auth::id() . '.' . $image->getClientOriginalExtension();
            $originalPath = $image->storeAs('diagnoses', $imageName, 'public');

            $aiResult = $aiService->diagnose($image);

            $gradCamPath = null;
            if (isset($aiResult['grad_cam_base64'])) {
                $base64Image = $aiResult['grad_cam_base64'];
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
                $gradCamName = 'gradcam_' . $imageName;
                Storage::disk('public')->put('diagnoses/' . $gradCamName, $imageData);
                $gradCamPath = 'diagnoses/' . $gradCamName;
            }

            $diagnosis = DB::transaction(function () use ($request, $aiResult, $originalPath, $gradCamPath) {
                $diagnosis = DiagnosisHistory::create([
                    'user_id' => Auth::id(),
                    'plant_id' => $request->plant_id,
                    'disease_name_technical' => $aiResult['disease_name_technical'],
                    'disease_name_arabic' => $aiResult['disease_name_arabic'],
                    'confidence_percentage' => $aiResult['confidence'],
                    'original_image_path' => $originalPath,
                    'grad_cam_image_path' => $gradCamPath,
                    'treatment' => $aiResult['treatment'],
                ]);

                if ($request->plant_id) {
                    $plant = Plant::find($request->plant_id);
                    if ($plant) {
                        $plant->update([
                            'health_status' => $aiResult['disease_name_technical'] ?? 'healthy'
                        ]);
                    }
                }

                return $diagnosis;
            });

            return $this->dataResponse($diagnosis);
        } catch (\Exception $e) {
            if (isset($originalPath)) Storage::disk('public')->delete($originalPath);
            if (isset($gradCamPath)) Storage::disk('public')->delete($gradCamPath);

            return $this->errorResponse($e->getMessage(), __('api.diagnosis_error'), 500);
        }
    }
}
