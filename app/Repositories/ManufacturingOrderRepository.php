<?php

namespace App\Repositories;

use App\Models\ManufacturingOrder;
use Illuminate\Database\Eloquent\Builder;

class ManufacturingOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ManufacturingOrder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = ManufacturingOrder::with([
            'product', 'bom', 'routing', 'warehouse', 'company', 'branch', 'creator',
        ]);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?ManufacturingOrder
    {
        return ManufacturingOrder::with([
            'product', 'bom', 'bom.lines.product', 'routing', 'routing.operations.workCenter',
            'warehouse', 'sourceLocation', 'destinationLocation', 'uom',
            'company', 'branch',
            'lines.product', 'lines.uom', 'lines.sourceLocation',
            'workOrders.workCenter', 'workOrders.operator',
            'productionCosts',
            'qualityChecks', 'scrapOrders',
            'approver', 'creator',
        ])->findOrFail($id);
    }

    public function getNextOrderNumber(): string
    {
        $last = ManufacturingOrder::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->order_number, 3) + 1 : 1;
        return 'MO-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('planned_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('planned_date', '<=', $filters['date_to']);
        }
    }
}
