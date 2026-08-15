<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\DispatchInventoryRequest;
use App\Models\HarvestedInventory;
use App\Models\InventoryUsage;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory.
     */
    #[QueryParameter('search', description: 'Search by storage location, date or plant name', type: 'string')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $inventory = HarvestedInventory::with('plant.crop:id,name_ar,name_en', 'plant:id,name,crop_id')
            ->where('current_quantity', '>', 0)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('storage_location', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%")
                        ->orWhere('harvest_quantity', 'like', "%{$search}%")
                        ->orWhere('current_quantity', 'like', "%{$search}%")
                        ->orWhereHas('plant', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage);

        return $this->paginatedResponse($inventory);
    }

    /**
     * Dispatch (sell/move) quantity from inventory.
     */
    public function dispatch(DispatchInventoryRequest $request, HarvestedInventory $inventory)
    {
        if ($inventory->current_quantity < $request->quantity_used)
            return $this->errorResponse('error', __('api.insufficient_inventory'), 422);

        DB::transaction(function () use ($inventory, $request) {
            InventoryUsage::create([
                'harvested_inventory_id' => $inventory->id,
                'user_id'                => $request->user()->id,
                'quantity_used'          => $request->quantity_used,
                'reason'                 => $request->reason,
            ]);

            $inventory->decrement('current_quantity', $request->quantity_used);
        });

        return $this->dataResponse($inventory->fresh()->load('plant'), __('api.inventory_dispatched'));
    }

    /**
     * Display inventory usage logs (Audit Trail).
     */
    #[QueryParameter('search', description: 'Search by reason, date, plant name, or user name', type: 'string')]
    #[QueryParameter('user_id', description: 'Filter by user ID', type: 'integer')]
    #[QueryParameter('per_page', description: 'Items per page', type: 'integer', default: 10)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function usageLog(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $logs = InventoryUsage::with(['inventory.plant:id,name', 'user:id,name'])
            ->when($request->filled('user_id'), fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('reason', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%")
                        ->orWhereHas('inventory.plant', fn($pq) => $pq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($perPage);

        return $this->paginatedResponse($logs);
    }
}
