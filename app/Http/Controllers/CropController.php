<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCropRequest;
use App\Http\Requests\UpdateCropRequest;
use App\Models\Crop;

class CropController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $crops = Crop::latest()->get();
        return $this->dataResponse($crops);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCropRequest $request)
    {
        $crop = Crop::create($request->validated());
        return $this->dataResponse($crop, __('api.created'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Crop $crop)
    {
        return $this->dataResponse($crop);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCropRequest $request, Crop $crop)
    {
        $crop->update($request->validated());
        return $this->dataResponse($crop, __('api.updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Crop $crop)
    {
        if ($crop->plants()->exists())
            return $this->errorResponse('error', __('api.crop_in_use'), 409);

        $crop->delete();
        return $this->successResponse(__('api.deleted'));
    }
}
