<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuditLogResource;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    #[QueryParameter('search', description: 'Search in action (updated, deleted) or entity type (Plant, User, Crop, Disease, IrrigationSchedule)', type: 'string')]
    #[QueryParameter('user_id', description: 'Filter logs by a specific user ID', type: 'integer')]
    #[QueryParameter('per_page', description: 'Number of items per page', type: 'integer', default: 20)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $logs = Activity::with('causer:id,name,role')
            ->when($request->filled('user_id'), fn($q) => $q->where('causer_id', $request->user_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                    ->orWhere('subject_type', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate($perPage);

        $logs->getCollection()->transform(function ($log) {
            return new AuditLogResource($log);
        });

        return $this->paginatedResponse($logs);
    }
}
