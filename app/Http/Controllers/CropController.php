<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCropRequest;
use App\Http\Requests\UpdateCropRequest;
use App\Models\Crop;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

class CropController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    #[QueryParameter('search', description: 'Search in crop names (ar/en)', type: 'string')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $crops = Crop::when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($innerQuery) use ($search) {
                $innerQuery->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%");
            });
        })
            ->latest()
            ->paginate($perPage);

        return $this->paginatedResponse($crops);
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
