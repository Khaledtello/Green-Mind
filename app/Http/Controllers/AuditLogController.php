<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\AuditLogResource;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    #[QueryParameter('search', description: 'Deep search in action, entity name, causer (name, role), or modified values', type: 'string', required: false)]
    #[QueryParameter('user_id', description: 'Filter logs by a specific user ID', type: 'integer')]
    #[QueryParameter('per_page', description: 'Number of items per page', type: 'integer', default: 20)]
    #[QueryParameter('page', description: 'Page number', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 100);

        $logs = Activity::with('causer:id,name,role')
            ->when($request->filled('user_id'), fn($q) => $q->where('causer_id', $request->user_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('description', 'like', "%{$search}%")
                        ->orWhere('properties', 'like', "%{$search}%")
                        ->orWhere('subject_type', "%{$search}%")
                        ->orWhereHas('causer', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                            
                            $roleEnum = UserRole::fromSearchTerm($search);
                            if ($roleEnum)
                                $cq->orWhere('role', $roleEnum->value);
                        });
                });
            })
            ->latest()
            ->paginate($perPage);

        $logs->getCollection()->transform(function ($log) {
            return new AuditLogResource($log);
        });

        return $this->paginatedResponse($logs);
    }
}
