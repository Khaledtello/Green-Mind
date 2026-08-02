<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Plant;
use App\Models\DiagnosisHistory;
use App\Models\IrrigationSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardData(): array
    {
        Carbon::setLocale(app()->getLocale());

        return [
            'kpis'                 => $this->getKpis(),
            'user_stats'           => $this->getUserStats(),
            'ai_performance'       => $this->getAiPerformance(),
            'disease_distribution' => $this->getDiseaseDistribution(),
            'confidence_ranges'    => $this->getConfidenceRanges(),
            'weekly_diagnoses'     => $this->getWeeklyDiagnoses(),
            'recent_diagnoses'     => $this->getRecentDiagnoses(),
            'top_diagnostics'      => $this->getTopDiagnostics(), 
            'upcoming_schedules'   => $this->getUpcomingSchedules(),
        ];
    }

    private function getKpis(): array
    {
        $activePlantsQuery = Plant::whereNull('harvest_date');

        return [
            'total_plants'    => (clone $activePlantsQuery)->count(),
            'healthy_plants'  => (clone $activePlantsQuery)->whereNull('disease_id')->count(),
            'diseased_plants' => (clone $activePlantsQuery)->whereNotNull('disease_id')->count(),
            'total_quantity'  => (clone $activePlantsQuery)->sum('quantity'),
        ];
    }

    private function getAiPerformance(): array
    {
        $totalDiagnoses = DiagnosisHistory::count();
        $avgConfidence = $totalDiagnoses > 0 ? DiagnosisHistory::latest()->take(100)->avg('confidence_percentage') : 0;

        return [
            'total_diagnoses' => $totalDiagnoses,
            'avg_confidence'  => round($avgConfidence, 2),
        ];
    }

    private function getDiseaseDistribution(): array
    {
        $topDiseases = Plant::whereNotNull('disease_id')
            ->whereNull('harvest_date')
            ->select('disease_id', DB::raw('count(*) as total'))
            ->groupBy('disease_id')
            ->orderByDesc('total')
            ->take(5)
            ->with('disease:id,ar_name,en_name')
            ->get();

        $distribution = $topDiseases->map(function ($item) {
            return [
                'name' => app()->getLocale() === 'ar'
                    ? ($item->disease->ar_name ?? $item->disease->technical_name)
                    : ($item->disease->en_name ?? $item->disease->technical_name),
                'count' => $item->total
            ];
        })->toArray();

        $totalDiseasedCount = Plant::whereNotNull('disease_id')->whereNull('harvest_date')->count();
        $othersCount = $totalDiseasedCount - $topDiseases->sum('total');

        if ($othersCount > 0)
            $diseaseDistribution[] = [
                'name'  => __('api.others'),
                'count' => $othersCount
            ];

        return $distribution;
    }

    private function getConfidenceRanges()
    {
        return DiagnosisHistory::selectRaw("
            SUM(CASE WHEN confidence_percentage < 40 THEN 1 ELSE 0 END) as less_than_40,
            SUM(CASE WHEN confidence_percentage BETWEEN 40 AND 59 THEN 1 ELSE 0 END) as from_40_to_59,
            SUM(CASE WHEN confidence_percentage BETWEEN 60 AND 79 THEN 1 ELSE 0 END) as from_60_to_79,
            SUM(CASE WHEN confidence_percentage BETWEEN 80 AND 89 THEN 1 ELSE 0 END) as from_80_to_89,
            SUM(CASE WHEN confidence_percentage >= 90 THEN 1 ELSE 0 END) as from_90_to_100
        ")->first();
    }

    private function getWeeklyDiagnoses(): array
    {
        $weeklyData = DiagnosisHistory::where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        $weeklyDiagnoses = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $weeklyDiagnoses[] = [
                'date'      => $date,
                'day_name'  => Carbon::parse($date)->translatedFormat('l'),
                'count'     => $weeklyData[$date] ?? 0
            ];
        }

        return $weeklyDiagnoses;
    }

    private function getRecentDiagnoses()
    {
        return DiagnosisHistory::with('plant:id,name')->latest()->take(5)->get();
    }

    private function getUpcomingSchedules()
    {
        return IrrigationSchedule::with('plant:id,name,crop_id', 'plant.crop:id,name_ar,name_en')
            ->whereNull('actual_date')
            ->where('recommended_date', '>=', today())
            ->orderBy('recommended_date')
            ->take(5)
            ->get();
    }

    private function getUserStats(): array
    {
        return [
            'total_users'     => User::count(),
            'engineers_count' => User::where('role', UserRole::Engineer)->count(),
            'farmers_count'   => User::where('role', UserRole::Farmer)->count(),
        ];
    }

    private function getTopDiagnostics()
    {
        return User::withCount('diagnoses')
            ->orderByDesc('diagnoses_count')
            ->take(5)
            ->get(['id', 'name', 'role']);
    }
}
