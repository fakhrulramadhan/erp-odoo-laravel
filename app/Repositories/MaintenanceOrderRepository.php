<?php

namespace App\Repositories;

use App\Models\MaintenanceOrder;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new MaintenanceOrder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = MaintenanceOrder::with(['equipment', 'assignee', 'vendor', 'company']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithRelations(int $id): ?MaintenanceOrder
    {
        return MaintenanceOrder::with([
            'equipment', 'equipment.workCenter', 'assignee', 'vendor', 'company', 'creator',
        ])->findOrFail($id);
    }

    public function getNextOrderNumber(): string
    {
        $last = MaintenanceOrder::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->order_number, 3) + 1 : 1;
        return 'MN-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('equipment', fn($eq) => $eq->where('name', 'like', "%{$filters['search']}%"));
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['maintenance_type'])) {
            $query->where('maintenance_type', $filters['maintenance_type']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('scheduled_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('scheduled_date', '<=', $filters['date_to']);
        }
    }
}
