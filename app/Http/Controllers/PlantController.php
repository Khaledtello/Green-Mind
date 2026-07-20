<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlantRequest;
use App\Http\Requests\UpdatePlantRequest;
use App\Models\Plant;
use Illuminate\Support\Facades\Auth;

class PlantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plants = Plant::with('crop:id,name_ar,name_en')->latest()->get();
        return $this->dataResponse($plants);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlantRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['user_id'] = Auth::id();

        $validatedData['health_status'] = 'healthy';

        $plant = Plant::create($validatedData);
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
        return $this->dataResponse($plant, __('api.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plant $plant)
    {
        $plant->delete();
        return $this->successResponse(__('api.deleted'));
    }
}
