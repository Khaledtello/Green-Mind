<?php

namespace App\Services;

use App\Models\IrrigationSchedule;
use App\Models\Plant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function calculateNextDate(Plant $plant, Carbon $baseDate): Carbon
    {
        $baseDays = $plant->crop->base_irrigation_days;
        return $baseDate->addDays($baseDays);
    }

    public function createInitialSchedule(Plant $plant): IrrigationSchedule
    {
        $today = Carbon::today();
        $nextDate = $this->calculateNextDate($plant, clone $today);

        return IrrigationSchedule::create([
            'plant_id' => $plant->id,
            'recommended_date' => $nextDate,
            'actual_date' => null,
            'is_manual_override' => false,
        ]);
    }

    public function completeIrrigation(IrrigationSchedule $schedule): IrrigationSchedule
    {
        $plant = $schedule->plant;
        $today = Carbon::today();

        $alreadyIrrigatedToday = $plant->irrigationSchedules()
            ->whereDate('actual_date', $today)
            ->exists();

        if ($alreadyIrrigatedToday)
            throw new \Exception(__('api.already_irrigated_today'));

        return DB::transaction(function () use ($schedule, $plant, $today) {
            $schedule->update(['actual_date' => $today]);

            $nextDate = $this->calculateNextDate($plant, clone $today);

            return IrrigationSchedule::create([
                'plant_id' => $plant->id,
                'recommended_date' => $nextDate,
                'actual_date' => null,
                'is_manual_override' => false,
            ]);
        });
    }
    public function undoIrrigation(Plant $plant): bool
    {
        return DB::transaction(function () use ($plant) {
            $lastCompleted = $plant->irrigationSchedules()
                ->whereNotNull('actual_date')
                ->latest('actual_date')
                ->first();

            if (!$lastCompleted)
                return false;

            $nextPending = $plant->irrigationSchedules()
                ->whereNull('actual_date')
                ->where('id', '>', $lastCompleted->id)
                ->first();

            if ($nextPending)
                $nextPending->delete();

            $lastCompleted->update(['actual_date' => null]);

            return true;
        });
    }
}
