<?php

namespace App\Services\Manufacturing;

use App\Enums\MaintenanceStatus;
use App\Enums\ManufacturingOrderStatus;
use App\Enums\QualityCheckStatus;
use App\Models\Asset;
use App\Models\Equipment;
use App\Models\MaintenanceOrder;
use App\Models\ManufacturingOrder;
use App\Models\ProductionCost;
use App\Models\QualityCheck;
use App\Models\ScrapOrder;
use App\Models\WorkCenter;
use Illuminate\Support\Facades\DB;

class ManufacturingDashboardService
{
    public function getSummary(): array
    {
        return [
            'manufacturing_orders' => $this->getManufacturingOrderSummary(),
            'quality_checks' => $this->getQualityCheckSummary(),
            'scrap' => $this->getScrapSummary(),
            'maintenance' => $this->getMaintenanceSummary(),
            'equipment' => $this->getEquipmentSummary(),
            'assets' => $this->getAssetSummary(),
            'production_costs' => $this->getProductionCostSummary(),
            'work_centers' => $this->getWorkCenterUtilization(),
        ];
    }

    protected function getManufacturingOrderSummary(): array
    {
        return [
            'total' => ManufacturingOrder::count(),
            'draft' => ManufacturingOrder::where('status', ManufacturingOrderStatus::Draft)->count(),
            'confirmed' => ManufacturingOrder::where('status', ManufacturingOrderStatus::Confirmed)->count(),
            'material_reserved' => ManufacturingOrder::where('status', ManufacturingOrderStatus::MaterialReserved)->count(),
            'in_production' => ManufacturingOrder::where('status', ManufacturingOrderStatus::InProduction)->count(),
            'quality_check' => ManufacturingOrder::where('status', ManufacturingOrderStatus::QualityCheck)->count(),
            'finished' => ManufacturingOrder::where('status', ManufacturingOrderStatus::Finished)->count(),
            'closed' => ManufacturingOrder::where('status', ManufacturingOrderStatus::Closed)->count(),
            'cancelled' => ManufacturingOrder::where('status', ManufacturingOrderStatus::Cancelled)->count(),
            'total_cost' => (float) ManufacturingOrder::whereNotIn('status', [ManufacturingOrderStatus::Cancelled])->sum('total_cost'),
            'avg_cost' => (float) ManufacturingOrder::whereNotIn('status', [ManufacturingOrderStatus::Cancelled])->avg('total_cost'),
        ];
    }

    protected function getQualityCheckSummary(): array
    {
        return [
            'total' => QualityCheck::count(),
            'draft' => QualityCheck::where('status', QualityCheckStatus::Draft)->count(),
            'in_progress' => QualityCheck::where('status', QualityCheckStatus::InProgress)->count(),
            'passed' => QualityCheck::where('status', QualityCheckStatus::Passed)->count(),
            'failed' => QualityCheck::where('status', QualityCheckStatus::Failed)->count(),
            'pass_rate' => $this->calculatePassRate(),
            'total_rejected_qty' => (float) QualityCheck::where('status', QualityCheckStatus::Failed)->sum('rejected_qty'),
        ];
    }

    protected function calculatePassRate(): float
    {
        $completed = QualityCheck::whereIn('status', [QualityCheckStatus::Passed, QualityCheckStatus::Failed])->count();
        if ($completed === 0) return 0;

        $passed = QualityCheck::where('status', QualityCheckStatus::Passed)->count();
        return round(($passed / $completed) * 100, 2);
    }

    protected function getScrapSummary(): array
    {
        return [
            'total' => ScrapOrder::count(),
            'total_cost' => (float) ScrapOrder::where('status', 'done')->sum('total_cost'),
            'total_qty' => (float) ScrapOrder::where('status', 'done')->sum('quantity'),
            'by_type' => ScrapOrder::where('status', 'done')
                ->select('scrap_type', DB::raw('count(*) as count'), DB::raw('sum(quantity) as total_qty'), DB::raw('sum(total_cost) as total_cost'))
                ->groupBy('scrap_type')
                ->get()
                ->toArray(),
        ];
    }

    protected function getMaintenanceSummary(): array
    {
        return [
            'total' => MaintenanceOrder::count(),
            'draft' => MaintenanceOrder::where('status', MaintenanceStatus::Draft)->count(),
            'requested' => MaintenanceOrder::where('status', MaintenanceStatus::Requested)->count(),
            'scheduled' => MaintenanceOrder::where('status', MaintenanceStatus::Scheduled)->count(),
            'in_progress' => MaintenanceOrder::where('status', MaintenanceStatus::InProgress)->count(),
            'completed' => MaintenanceOrder::where('status', MaintenanceStatus::Completed)->count(),
            'overdue' => MaintenanceOrder::where('status', MaintenanceStatus::Scheduled)
                ->where('scheduled_date', '<', now())
                ->count(),
        ];
    }

    protected function getEquipmentSummary(): array
    {
        return [
            'total' => Equipment::count(),
            'active' => Equipment::where('status', 'active')->count(),
            'in_maintenance' => Equipment::where('status', 'in_maintenance')->count(),
            'needs_maintenance' => Equipment::whereNotNull('next_maintenance_date')
                ->where('next_maintenance_date', '<=', now()->addDays(7))
                ->count(),
        ];
    }

    protected function getAssetSummary(): array
    {
        return [
            'total' => Asset::count(),
            'active' => Asset::where('status', \App\Enums\AssetStatus::Active)->count(),
            'total_acquisition_cost' => (float) Asset::sum('acquisition_cost'),
            'total_accumulated_depreciation' => (float) Asset::sum('accumulated_depreciation'),
            'total_book_value' => (float) Asset::sum('book_value'),
            'by_category' => Asset::where('status', \App\Enums\AssetStatus::Active)
                ->select(
                    'asset_category_id',
                    DB::raw('count(*) as count'),
                    DB::raw('sum(acquisition_cost) as total_cost'),
                    DB::raw('sum(book_value) as total_book_value')
                )
                ->groupBy('asset_category_id')
                ->with('category:id,name')
                ->get()
                ->toArray(),
        ];
    }

    protected function getProductionCostSummary(): array
    {
        $currentMonth = now()->format('Y-m');

        return [
            'current_month' => [
                'total' => (float) ProductionCost::where('created_at', 'like', "{$currentMonth}%")->sum('total_cost'),
                'material' => (float) ProductionCost::where('cost_type', 'material')->where('created_at', 'like', "{$currentMonth}%")->sum('total_cost'),
                'labor' => (float) ProductionCost::where('cost_type', 'labor')->where('created_at', 'like', "{$currentMonth}%")->sum('total_cost'),
                'machine' => (float) ProductionCost::where('cost_type', 'machine')->where('created_at', 'like', "{$currentMonth}%")->sum('total_cost'),
                'overhead' => (float) ProductionCost::where('cost_type', 'overhead')->where('created_at', 'like', "{$currentMonth}%")->sum('total_cost'),
            ],
            'all_time' => [
                'total' => (float) ProductionCost::sum('total_cost'),
                'material' => (float) ProductionCost::where('cost_type', 'material')->sum('total_cost'),
                'labor' => (float) ProductionCost::where('cost_type', 'labor')->sum('total_cost'),
                'machine' => (float) ProductionCost::where('cost_type', 'machine')->sum('total_cost'),
                'overhead' => (float) ProductionCost::where('cost_type', 'overhead')->sum('total_cost'),
            ],
        ];
    }

    protected function getWorkCenterUtilization(): array
    {
        $workCenters = WorkCenter::with('routingOperations')->get()->map(function ($wc) {
            $totalHours = $wc->routingOperations->sum('duration_minutes') / 60;
            return [
                'id' => $wc->id,
                'name' => $wc->name,
                'code' => $wc->code,
                'capacity_per_hour' => (float) $wc->capacity_per_hour,
                'efficiency' => (float) $wc->efficiency,
                'cost_per_hour' => (float) $wc->cost_per_hour,
                'total_routed_hours' => round($totalHours, 2),
            ];
        });

        return $workCenters->toArray();
    }

    public function getProductionTrend(int $months = 12): array
    {
        return ManufacturingOrder::select(
            DB::raw("DATE_FORMAT(actual_finish, '%Y-%m') as month"),
            DB::raw('count(*) as orders_count'),
            DB::raw('sum(produced_qty) as total_produced'),
            DB::raw('sum(total_cost) as total_cost')
        )
        ->whereNotNull('actual_finish')
        ->where('actual_finish', '>=', now()->subMonths($months))
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->toArray();
    }
}
