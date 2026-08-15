<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Requests\UpdateDiseaseRequest;
use App\Models\Disease;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the diseases.
     */
    #[QueryParameter('search', description: 'Search in disease names (ar/en/technical)', type: 'string')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $diseases = Disease::when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($innerQuery) use ($search) {
                $innerQuery->where('ar_name', 'like', "%{$search}%")
                    ->orWhere('en_name', 'like', "%{$search}%")
                    ->orWhere('technical_name', 'like', "%{$search}%");
            });
        })
            ->latest()
            ->paginate($perPage);

        return $this->paginatedResponse($diseases);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDiseaseRequest $request)
    {
        $disease = Disease::create($request->validated());
        return $this->dataResponse($disease, __('api.created'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Disease $disease)
    {
        return $this->dataResponse($disease);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDiseaseRequest $request, Disease $disease)
    {
        $disease->update($request->validated());
        return $this->dataResponse($disease, __('api.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Disease $disease)
    {
        $disease->delete();
        return $this->successResponse(__('api.deleted'));
    }
}
