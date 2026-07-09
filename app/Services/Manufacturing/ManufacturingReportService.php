<?php

namespace App\Services\Manufacturing;

use App\Models\Asset;
use App\Models\ManufacturingOrder;
use App\Models\MaintenanceOrder;
use App\Models\ProductionCost;
use App\Models\QualityCheck;
use App\Models\ScrapOrder;
use Illuminate\Support\Facades\DB;

class ManufacturingReportService
{
    public function getProductionReport(array $filters = []): array
    {
        $query = ManufacturingOrder::query()
            ->with(['product:id,name,sku', 'bom:id,bom_number', 'workCenter:id,name'])
            ->whereNotIn('status', [\App\Enums\ManufacturingOrderStatus::Cancelled]);

        if (!empty($filters['date_from'])) {
            $query->where('planned_start', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('planned_finish', '<=', $filters['date_to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['work_center_id'])) {
            $query->where('work_center_id', $filters['work_center_id']);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return [
            'data' => $orders->map(function ($mo) {
                return [
                    'order_number' => $mo->order_number,
                    'product' => $mo->product?->name,
                    'bom' => $mo->bom?->bom_number,
                    'planned_qty' => (float) $mo->quantity,
                    'produced_qty' => (float) $mo->produced_qty,
                    'scrap_qty' => (float) $mo->scrap_qty,
                    'total_cost' => (float) $mo->total_cost,
                    'unit_cost' => (float) $mo->unit_cost,
                    'status' => $mo->status->label(),
                    'planned_start' => $mo->planned_start,
                    'planned_finish' => $mo->planned_finish,
                    'actual_start' => $mo->actual_start,
                    'actual_finish' => $mo->actual_finish,
                    'efficiency' => $this->calculateEfficiency($mo),
                ];
            }),
            'summary' => [
                'total_orders' => $orders->count(),
                'total_planned_qty' => $orders->sum('quantity'),
                'total_produced_qty' => $orders->sum('produced_qty'),
                'total_scrap_qty' => $orders->sum('scrap_qty'),
                'total_cost' => $orders->sum('total_cost'),
                'avg_unit_cost' => $orders->avg('unit_cost'),
                'overall_efficiency' => $orders->count() > 0 ? round($orders->sum('produced_qty') / max($orders->sum('quantity'), 1) * 100, 2) : 0,
            ],
        ];
    }

    public function getQualityReport(array $filters = []): array
    {
        $query = QualityCheck::query()
            ->with(['product:id,name,sku', 'inspector:id,name'])
            ->whereNotNull('inspection_date');

        if (!empty($filters['date_from'])) {
            $query->where('inspection_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('inspection_date', '<=', $filters['date_to']);
        }

        $checks = $query->orderBy('inspection_date', 'desc')->get();

        $byStatus = $checks->groupBy(fn ($c) => $c->status->value);

        return [
            'data' => $checks->map(function ($qc) {
                return [
                    'check_number' => $qc->check_number,
                    'product' => $qc->product?->name,
                    'inspector' => $qc->inspector?->name,
                    'quantity' => (float) $qc->quantity,
                    'result' => $qc->result,
                    'rejected_qty' => (float) $qc->rejected_qty,
                    'inspection_date' => $qc->inspection_date,
                    'status' => $qc->status->label(),
                ];
            }),
            'summary' => [
                'total_checks' => $checks->count(),
                'passed' => $checks->where('result', 'pass')->count(),
                'failed' => $checks->where('result', 'fail')->count(),
                'pass_rate' => $checks->count() > 0
                    ? round($checks->where('result', 'pass')->count() / $checks->count() * 100, 2)
                    : 0,
                'total_inspected' => $checks->sum('quantity'),
                'total_rejected' => $checks->sum('rejected_qty'),
            ],
        ];
    }

    public function getCostReport(array $filters = []): array
    {
        $query = ProductionCost::query()
            ->with(['manufacturingOrder:id,order_number', 'product:id,name,sku', 'workCenter:id,name']);

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['cost_type'])) {
            $query->where('cost_type', $filters['cost_type']);
        }

        $costs = $query->orderBy('created_at', 'desc')->get();

        $byType = $costs->groupBy('cost_type');

        return [
            'data' => $costs->map(function ($pc) {
                return [
                    'mo_number' => $pc->manufacturingOrder?->order_number,
                    'product' => $pc->product?->name,
                    'work_center' => $pc->workCenter?->name,
                    'cost_type' => $pc->cost_type,
                    'description' => $pc->description,
                    'quantity' => (float) $pc->quantity,
                    'unit_cost' => (float) $pc->unit_cost,
                    'total_cost' => (float) $pc->total_cost,
                    'date' => $pc->created_at->toDateString(),
                ];
            }),
            'by_type' => $byType->map(fn ($group) => [
                'count' => $group->count(),
                'total_cost' => (float) $group->sum('total_cost'),
            ])->toArray(),
            'summary' => [
                'total_records' => $costs->count(),
                'total_cost' => (float) $costs->sum('total_cost'),
                'material' => (float) $costs->where('cost_type', 'material')->sum('total_cost'),
                'labor' => (float) $costs->where('cost_type', 'labor')->sum('total_cost'),
                'machine' => (float) $costs->where('cost_type', 'machine')->sum('total_cost'),
                'overhead' => (float) $costs->where('cost_type', 'overhead')->sum('total_cost'),
            ],
        ];
    }

    public function getMaintenanceReport(array $filters = []): array
    {
        $query = MaintenanceOrder::query()
            ->with(['equipment:id,name,code', 'assignedTo:id,name']);

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['maintenance_type'])) {
            $query->where('maintenance_type', $filters['maintenance_type']);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return [
            'data' => $orders->map(function ($mo) {
                return [
                    'order_number' => $mo->order_number,
                    'equipment' => $mo->equipment?->name,
                    'maintenance_type' => $mo->maintenance_type,
                    'priority' => $mo->priority,
                    'status' => $mo->status->label(),
                    'scheduled_date' => $mo->scheduled_date,
                    'actual_duration' => $mo->actual_duration,
                    'cost' => (float) $mo->cost,
                    'assigned_to' => $mo->assignedTo?->name,
                ];
            }),
            'summary' => [
                'total' => $orders->count(),
                'preventive' => $orders->where('maintenance_type', 'preventive')->count(),
                'corrective' => $orders->where('maintenance_type', 'corrective')->count(),
                'total_cost' => (float) $orders->sum('cost'),
                'avg_duration' => round($orders->whereNotNull('actual_duration')->avg('actual_duration') ?? 0, 2),
                'completed' => $orders->where('status', \App\Enums\MaintenanceStatus::Completed)->count(),
            ],
        ];
    }

    public function getAssetReport(array $filters = []): array
    {
        $query = Asset::query()->with('category:id,name');

        if (!empty($filters['asset_category_id'])) {
            $query->where('asset_category_id', $filters['asset_category_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        $assets = $query->orderBy('asset_number')->get();

        return [
            'data' => $assets->map(function ($a) {
                return [
                    'asset_number' => $a->asset_number,
                    'name' => $a->name,
                    'category' => $a->category?->name,
                    'acquisition_date' => $a->acquisition_date,
                    'acquisition_cost' => (float) $a->acquisition_cost,
                    'accumulated_depreciation' => (float) $a->accumulated_depreciation,
                    'book_value' => (float) $a->book_value,
                    'status' => $a->status->label(),
                    'depreciation_method' => $a->depreciation_method->label(),
                    'useful_life_months' => $a->useful_life_months,
                ];
            }),
            'summary' => [
                'total_assets' => $assets->count(),
                'total_acquisition_cost' => (float) $assets->sum('acquisition_cost'),
                'total_accumulated_depreciation' => (float) $assets->sum('accumulated_depreciation'),
                'total_book_value' => (float) $assets->sum('book_value'),
                'by_category' => $assets->groupBy('asset_category_id')->map(fn ($group) => [
                    'count' => $group->count(),
                    'acquisition_cost' => (float) $group->sum('acquisition_cost'),
                    'book_value' => (float) $group->sum('book_value'),
                ])->toArray(),
            ],
        ];
    }

    public function getScrapReport(array $filters = []): array
    {
        $query = ScrapOrder::query()
            ->with(['product:id,name,sku', 'location:id,name'])
            ->where('status', 'done');

        if (!empty($filters['date_from'])) {
            $query->where('scrap_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('scrap_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['scrap_type'])) {
            $query->where('scrap_type', $filters['scrap_type']);
        }

        $scraps = $query->orderBy('scrap_date', 'desc')->get();

        return [
            'data' => $scraps->map(function ($s) {
                return [
                    'scrap_number' => $s->scrap_number,
                    'product' => $s->product?->name,
                    'location' => $s->location?->name,
                    'scrap_type' => $s->scrap_type,
                    'quantity' => (float) $s->quantity,
                    'unit_cost' => (float) $s->unit_cost,
                    'total_cost' => (float) $s->total_cost,
                    'reason' => $s->reason,
                    'scrap_date' => $s->scrap_date,
                ];
            }),
            'summary' => [
                'total_records' => $scraps->count(),
                'total_quantity' => (float) $scraps->sum('quantity'),
                'total_cost' => (float) $scraps->sum('total_cost'),
                'by_type' => $scraps->groupBy('scrap_type')->map(fn ($group) => [
                    'count' => $group->count(),
                    'quantity' => (float) $group->sum('quantity'),
                    'cost' => (float) $group->sum('total_cost'),
                ])->toArray(),
            ],
        ];
    }

    protected function calculateEfficiency(ManufacturingOrder $mo): ?float
    {
        if ((float) $mo->quantity <= 0 || (float) $mo->produced_qty <= 0) return null;
        return round(((float) $mo->produced_qty / (float) $mo->quantity) * 100, 2);
    }
}
