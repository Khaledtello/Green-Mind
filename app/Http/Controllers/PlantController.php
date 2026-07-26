<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Http\Requests\UpdatePlantRequest;
use App\Models\Plant;
use App\Services\ScheduleService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlantController extends Controller
{
    public function __construct(private ScheduleService $scheduleService) {}

    /**
     * Display a listing of the resource.
     */
    #[QueryParameter('page', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $perPage = min($perPage, 100);

        $plants = Plant::with('crop:id,name_ar,name_en')->latest()->paginate($perPage);
        return $this->paginatedResponse($plants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlantRequest $request)
    {
        $plant = DB::transaction(function () use ($request) {
            $request['user_id'] = Auth::id();
            $plant = Plant::create($request->all());
            $this->scheduleService->createInitialSchedule($plant);
            return $plant;
        });

        return $this->dataResponse($plant, __('api.created'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Plant $plant)
    {
        return $this->dataResponse($plant->load('crop'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantRequest $request, Plant $plant)
    {
        $plant->update($request->validated());
        return $this->dataResponse($plant->load('crop'), __('api.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plant $plant)
    {
        DB::transaction(function () use ($plant) {
            $plant->irrigationSchedules()->whereNull('actual_date')->delete();
            $plant->delete();
        });

        return $this->successResponse(__('api.deleted'));
    }
}
