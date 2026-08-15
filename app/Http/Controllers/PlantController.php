<?php

namespace App\Http\Controllers;

use App\Http\Requests\HarvestRequest;
use App\Http\Requests\StorePlantRequest;
use App\Http\Requests\UpdatePlantRequest;
use App\Models\HarvestedInventory;
use App\Models\IrrigationSchedule;
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
    #[QueryParameter('search', description: 'Deep search in name, quantity, base irrigation days, notes, dates, crop, or disease', type: 'string')]
    #[QueryParameter('crop_id', description: 'Filter by crop ID', type: 'integer')]
    #[QueryParameter('is_healthy', description: 'Filter by health status (true/false)', type: 'boolean')]
    #[QueryParameter('is_harvested', description: 'Filter by harvest status (true=harvested, false=active)', type: 'boolean')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $query = Plant::with('crop', 'disease');

        $activeQuery = clone $query;
        $activeQuery->whereNull('harvest_date');

        $stats = [
            'total'                 => (clone $query)->count(),
            'in_field'              => (clone $activeQuery)->count(),
            'harvested'             => (clone $query)->whereNotNull('harvest_date')->count(),
            'healthy'               => (clone $activeQuery)->whereNull('disease_id')->count(),
            'diseased'              => (clone $activeQuery)->whereNotNull('disease_id')->count(),
            'irrigations_due_today' => IrrigationSchedule::whereNull('actual_date')->whereDate('recommended_date', today())->count(),
        ];

        $query->when($request->filled('crop_id'), fn($q) => $q->where('crop_id', $request->crop_id));

        $query->when($request->has('is_healthy'), function ($q) use ($request) {
            $request->boolean('is_healthy')
                ? $q->whereNull('disease_id')
                : $q->whereNotNull('disease_id');
        });

        $query->when($request->has('is_harvested'), function ($q) use ($request) {
            $request->boolean('is_harvested')
                ? $q->whereNotNull('harvest_date')
                : $q->whereNull('harvest_date');
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;

            $q->where(function ($innerQuery) use ($search) {
                $innerQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('quantity', 'like', "%{$search}%")
                    ->orWhere('base_irrigation_days', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('planting_date', 'like', "%{$search}%")
                    ->orWhere('harvest_date', 'like', "%{$search}%");

                $innerQuery->orWhereHas('crop', function ($cropQuery) use ($search) {
                    $cropQuery->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });

                $innerQuery->orWhereHas('disease', function ($diseaseQuery) use ($search) {
                    $diseaseQuery->where('ar_name', 'like', "%{$search}%")
                        ->orWhere('en_name', 'like', "%{$search}%")
                        ->orWhere('technical_name', 'like', "%{$search}%");
                });
            });
        });

        $perPage = min((int) $request->input('per_page', 10), 100);
        $plants = $query->latest()->paginate($perPage);

        return $this->paginatedResponse($plants, ['stats' => $stats]);
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
        return $this->dataResponse($plant->load('crop', 'disease'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePlantRequest $request, Plant $plant)
    {
        if ($plant->harvest_date !== null)
            return $this->errorResponse('error', __('api.batch_locked'), 403);

        $plant->update($request->validated());
        return $this->dataResponse($plant->load('crop', 'disease'), __('api.updated'));
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
    public function harvest(HarvestRequest $request, Plant $plant)
    {
        if ($plant->harvest_date !== null)
            return $this->errorResponse('error', __('api.already_harvested'), 400);

        DB::transaction(function () use ($request, $plant) {
            $plant->update(['harvest_date' => now()->toDateString()]);
            $plant->irrigationSchedules()->whereNull('actual_date')->delete();

            HarvestedInventory::create([
                'plant_id'         => $plant->id,
                'harvest_quantity' => $request->harvest_quantity,
                'current_quantity' => $request->harvest_quantity,
                'storage_location' => $request->storage_location,
            ]);
        });

        return $this->dataResponse($plant->load('crop'), __('api.harvested'));
    }
}
