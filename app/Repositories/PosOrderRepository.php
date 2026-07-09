<?php

namespace App\Repositories;

use App\Models\PosOrder;
use Illuminate\Database\Eloquent\Builder;

class PosOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PosOrder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PosOrder::with(['posSession', 'customer', 'warehouse', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?PosOrder
    {
        return PosOrder::with([
            'posSession', 'customer', 'warehouse', 'company', 'branch',
            'lines.product', 'payments.paymentMethod', 'creator',
        ])->findOrFail($id);
    }

    public function getNextOrderNumber(): string
    {
        $last = PosOrder::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->order_number, 4) + 1 : 1;
        return 'POS-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['pos_session_id'])) {
            $query->where('pos_session_id', $filters['pos_session_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
    }
}
