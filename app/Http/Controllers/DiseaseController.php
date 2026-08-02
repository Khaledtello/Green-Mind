<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiseaseRequest;
use App\Http\Requests\UpdateDiseaseRequest;
use App\Models\Disease;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->dataResponse(Disease::all());
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
