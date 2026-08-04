<?php

namespace App\Http\Controllers;

use App\Http\Requests\RescheduleRequest;
use App\Models\IrrigationSchedule;
use App\Models\Plant;
use App\Services\ScheduleService;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(private ScheduleService $scheduleService) {}

    /**
     * Display upcoming irrigation schedules.
     */
    #[QueryParameter('page', type: 'integer', default: 1)]
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $perPage = min($perPage, 100);
        
        $schedules = IrrigationSchedule::with('plant.crop')
            ->whereNull('actual_date')
            ->latest('recommended_date')
            ->paginate($perPage);

        return $this->paginatedResponse($schedules);
    }

    /**
     * Manually reschedule an upcoming irrigation date.
     */
    public function reschedule(RescheduleRequest $request, IrrigationSchedule $schedule)
    {
        if ($schedule->actual_date !== null)
            return $this->errorResponse('error', __('api.cannot_edit_past'), 403);

        $schedule->update([
            'recommended_date' => $request->recommended_date,
            'is_manual_override' => true,
        ]);

        return $this->dataResponse($schedule, __('api.schedule_updated'));
    }

    /**
     * Mark an irrigation as completed and generate the next one.
     */
    public function irrigate(IrrigationSchedule $schedule)
    {
        if ($schedule->actual_date !== null)
            return $this->errorResponse('error', __('api.already_irrigated'), 400);

        try {
            $nextSchedule = $this->scheduleService->completeIrrigation($schedule);
        } catch (\Exception $e) {
            return $this->errorResponse('error', $e->getMessage(), 400);
        }

        return $this->dataResponse([
            'completed_schedule' => $schedule->refresh(),
            'next_schedule' => $nextSchedule
        ], __('api.irrigation_completed'));
    }

    /**
     * Undo the last completed irrigation for a specific plant batch.
     */
    public function undo(Plant $plant)
    {
        $success = $this->scheduleService->undoIrrigation($plant);

        if (!$success)
            return $this->errorResponse('error', __('api.no_irrigation_to_undo'), 404);

        return $this->successResponse(__('api.undo_successful'));
    }
}
