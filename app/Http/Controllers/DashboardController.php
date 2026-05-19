<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Repositories\DashboardRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $dashboardRepository;
    public function __construct(DashboardRepository $dashboardRepository)
    {
        $this->dashboardRepository = $dashboardRepository;
    }

    public function dashboardAdmin(Request $request)
    {
        $year = $request->year ?? now()->year;
        $data = $this->dashboardRepository->adminDashboard($year);
        return ApiResponse::success($data, 'Dashboard data retrieved successfully');
    }

    public function attendanceSummary()
    {
        $data = $this->dashboardRepository->getAttendanceSummary();
        return ApiResponse::success($data, 'Attendance summary retrieved successfully');
    }
}
