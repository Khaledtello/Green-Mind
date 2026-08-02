<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    /**
     * Get dashboard statistics and KPIs.
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData();
        return $this->dataResponse($data);
    }
}
