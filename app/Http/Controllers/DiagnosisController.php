<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\DiagnosisRequest;
use App\Models\DiagnosisHistory;
use App\Models\Disease;
use App\Models\Plant;
use App\Services\AIService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiagnosisController extends Controller
{
    /**
     * Display a listing of the diagnosis history.
     */
    #[QueryParameter('search', description: 'Search in disease name or treatment', type: 'string')]
    #[QueryParameter('plant_id', description: 'Filter by specific plant batch', type: 'integer')]
    #[QueryParameter('user_id', description: 'Filter by diagnosing user (Admin/Engineer only)', type: 'integer')]
    #[QueryParameter('is_healthy', description: 'Filter by health status (true=healthy, false=diseased)', type: 'boolean')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $query = DiagnosisHistory::with(['plant:id,name', 'user:id,name']);

        if ($request->user()->role === UserRole::Farmer)
            $query->where('user_id', $request->user()->id);

        else if ($request->filled('user_id'))
            $query->where('user_id', $request->user_id);

        $query->when($request->filled('plant_id'), fn($q) => $q->where('plant_id', $request->plant_id));

        $query->when($request->has('is_healthy'), function ($q) use ($request) {
            $request->boolean('is_healthy')
                ? $q->where('disease_name_technical', 'like', '%healthy%')
                : $q->where('disease_name_technical', 'not like', '%healthy%');
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where('disease_name_arabic', 'like', "%{$request->search}%")
                ->orWhere('disease_name_english', 'like', "%{$request->search}%")
                ->orWhere('disease_name_technical', 'like', "%{$request->search}%")
                ->orWhere('treatment', 'like', "%{$request->search}%");
        });

        $perPage = min((int) $request->input('per_page', 10), 100);
        $diagnoses = $query->latest()->paginate($perPage);;

        return $this->paginatedResponse($diagnoses);
    }

    /**
     * Diagnose plant disease from an uploaded image.
     * 
     * Upload a plant leaf image to get the diagnosis, confidence score, Grad-CAM heatmap, and treatment.
     * Optionally link the diagnosis to a specific plant batch to automatically update its health status.
     */
    public function predict(DiagnosisRequest $request, AIService $aiService)
    {
        try {
            $image = $request->file('image');
            $plant_id = $request->plant_id;

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

            $diagnosis = DB::transaction(function () use ($plant_id, $aiResult, $originalPath, $gradCamPath) {
                $diagnosis = DiagnosisHistory::create([
                    'user_id'                => Auth::id(),
                    'plant_id'               => $plant_id,
                    'disease_name_technical' => $aiResult['disease_name_technical'],
                    'disease_name_arabic'    => $aiResult['disease_name_arabic'],
                    'disease_name_english'   => $aiResult['disease_name_english'],
                    'confidence_percentage'  => $aiResult['confidence'],
                    'original_image_path'    => $originalPath,
                    'grad_cam_image_path'    => $gradCamPath,
                    'treatment'              => $aiResult['treatment'],
                ]);

                if ($plant_id) {
                    $plant = Plant::find($plant_id);
                    $technicalName = $aiResult['disease_name_technical'];

                    $lowerTechnicalName = strtolower($technicalName);
                    $isHealthy = str_contains($lowerTechnicalName, 'unknown')
                        || str_contains($lowerTechnicalName, 'healthy');

                    if ($isHealthy)
                        $plant->update(['disease_id' => null]);

                    else {
                        $disease = Disease::firstOrCreate(
                            ['technical_name' => $technicalName],
                            [
                                'ar_name' => $aiResult['disease_name_arabic'],
                                'en_name' => $aiResult['disease_name_english'],
                            ]
                        );

                        $plant->update(['disease_id' => $disease->id]);
                    }
                }

                return $diagnosis->refresh();
            });

            return $this->dataResponse([
                'diagnosis'               => $diagnosis,
                'schedule_recommendation' => [
                    'recommended_interval_days' => $aiResult['schedule_recommendation']['recommended_interval_days'],
                    'reason'                    => $aiResult['schedule_recommendation']['reason'],
                ],
                'details'                 => $aiResult['details'],
                'top_predictions'         => $aiResult['top_predictions'],
            ]);
        } catch (\Exception $e) {
            if (isset($originalPath)) Storage::disk('public')->delete($originalPath);
            if (isset($gradCamPath)) Storage::disk('public')->delete($gradCamPath);

            return $this->errorResponse($e->getMessage(), __('api.diagnosis_error'), 500);
        }
    }
}
