<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PurchaseOrder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PurchaseOrder::with(['vendor', 'company', 'branch', 'currency', 'warehouse', 'approver', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?PurchaseOrder
    {
        return PurchaseOrder::with([
            'vendor', 'company', 'currency', 'warehouse',
            'lines.product', 'lines.uom', 'lines.tax',
            'approver', 'creator',
        ])->findOrFail($id);
    }

    public function getNextOrderNumber(): string
    {
        $last = PurchaseOrder::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->order_number, 3) + 1 : 1;
        return 'PO-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('order_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('order_date', '<=', $filters['date_to']);
        }
    }
}
