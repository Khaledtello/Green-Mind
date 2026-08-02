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
    #[QueryParameter('page', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $perPage = min($perPage, 100);

        $logs = Activity::with('causer:id,name,role')->latest()->paginate($perPage);

        $logs->getCollection()->transform(function ($log) {
            return new AuditLogResource($log);
        });
        
        return $this->paginatedResponse($logs);
    }
}
