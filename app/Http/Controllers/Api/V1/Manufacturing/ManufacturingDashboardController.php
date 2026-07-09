<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Services\Manufacturing\ManufacturingDashboardService;
use App\Services\Manufacturing\ManufacturingReportService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManufacturingDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ManufacturingDashboardService $dashboardService,
        protected ManufacturingReportService $reportService,
    ) {}

    public function summary(): JsonResponse
    {
        $data = $this->dashboardService->getSummary();
        return $this->success($data);
    }

    public function productionTrend(Request $request): JsonResponse
    {
        $months = $request->input('months', 12);
        $data = $this->dashboardService->getProductionTrend($months);
        return $this->success($data);
    }

    public function productionReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'status', 'product_id', 'work_center_id']);
        $data = $this->reportService->getProductionReport($filters);
        return $this->success($data);
    }

    public function qualityReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to']);
        $data = $this->reportService->getQualityReport($filters);
        return $this->success($data);
    }

    public function costReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'cost_type']);
        $data = $this->reportService->getCostReport($filters);
        return $this->success($data);
    }

    public function maintenanceReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'maintenance_type']);
        $data = $this->reportService->getMaintenanceReport($filters);
        return $this->success($data);
    }

    public function assetReport(Request $request): JsonResponse
    {
        $filters = $request->only(['asset_category_id', 'status', 'branch_id']);
        $data = $this->reportService->getAssetReport($filters);
        return $this->success($data);
    }

    public function scrapReport(Request $request): JsonResponse
    {
        $filters = $request->only(['date_from', 'date_to', 'scrap_type']);
        $data = $this->reportService->getScrapReport($filters);
        return $this->success($data);
    }
}
