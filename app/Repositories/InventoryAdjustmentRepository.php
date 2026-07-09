<?php

namespace App\Repositories;

use App\Models\InventoryAdjustment;
use Illuminate\Database\Eloquent\Builder;

class InventoryAdjustmentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new InventoryAdjustment());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = InventoryAdjustment::with(['company', 'warehouse', 'validator', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?InventoryAdjustment
    {
        return InventoryAdjustment::with([
            'company', 'warehouse', 'validator', 'creator',
            'lines.product', 'lines.location', 'lines.lot',
        ])->findOrFail($id);
    }

    public function getNextAdjustmentNumber(): string
    {
        $last = InventoryAdjustment::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->adjustment_number, 4) + 1 : 1;
        return 'ADJ-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('adjustment_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('adjustment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('adjustment_date', '<=', $filters['date_to']);
        }
    }
}
