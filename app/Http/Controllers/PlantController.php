<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Http\Requests\UpdatePlantDiseaseRequest;
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
    #[QueryParameter('search', description: 'Search in name or notes', type: 'string')]
    #[QueryParameter('crop_id', description: 'Filter by crop ID', type: 'integer')]
    #[QueryParameter('is_healthy', description: 'Filter by health status (true/false)', type: 'boolean')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $plants = Plant::with('crop', 'disease')
            ->when($request->filled('crop_id'), fn($q) => $q->where('crop_id', $request->crop_id))
            ->when($request->has('is_healthy'), function ($q) use ($request) {
                $request->boolean('is_healthy')
                    ? $q->whereNull('disease_id')
                    : $q->whereNotNull('disease_id');
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('notes', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate($perPage);

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
            $this->scheduleService->generateNextSchedule($plant);
            return $plant;
        });

        return $this->dataResponse($plant, __('api.created'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Plant $plant)
    {
        return $this->dataResponse($plant->load(['crop', 'disease']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantRequest $request, Plant $plant)
    {
        if ($plant->harvest_date !== null)
            return $this->errorResponse('error', __('api.batch_locked'), 403);

        $plant->update($request->validated());
        return $this->dataResponse($plant->load('crop'), __('api.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plant $plant)
    {
        DB::transaction(function () use ($plant) {
            $plant->delete();
            $plant->irrigationSchedules()->whereNull('actual_date')->delete();
        });

        return $this->successResponse(__('api.deleted'));
    }

    /**
     * Mark the plant batch as harvested.
     */
    public function harvest(Plant $plant)
    {
        if ($plant->harvest_date !== null)
            return $this->errorResponse('error', __('api.already_harvested'), 400);

        DB::transaction(function () use ($plant) {
            $plant->update(['harvest_date' => now()->toDateString()]);
            $plant->irrigationSchedules()->whereNull('actual_date')->delete();
        });

        return $this->dataResponse($plant->load('crop'), __('api.harvested'));
    }

    /**
     * Undo the harvest for a plant batch.
     */
    public function undoHarvest(Plant $plant)
    {
        if ($plant->harvest_date === null)
            return $this->errorResponse('error', __('api.not_harvested'), 400);

        DB::transaction(function () use ($plant) {
            $plant->update(['harvest_date' => null]);
            $this->scheduleService->generateNextSchedule($plant);
        });

        return $this->dataResponse($plant->load('crop'), __('api.undo_harvest'));
    }

    /**
     * Manually update the disease status of a plant batch.
     */
    public function updateDisease(UpdatePlantDiseaseRequest $request, Plant $plant)
    {
        if ($plant->harvest_date !== null)
            return $this->errorResponse('error', __('api.batch_locked'), 403);

        $plant->update($request->validated());
        return $this->dataResponse($plant->load('crop', 'disease'), __('api.updated'));
    }
}
