<?php

namespace App\Repositories;

use App\Models\PurchaseRequisition;
use Illuminate\Database\Eloquent\Builder;

class PurchaseRequisitionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PurchaseRequisition());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PurchaseRequisition::with(['company', 'branch', 'department', 'currency', 'approver', 'purchaseOrder', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?PurchaseRequisition
    {
        return PurchaseRequisition::with([
            'company', 'branch', 'department', 'currency',
            'approver', 'purchaseOrder', 'creator',
            'lines.product', 'lines.uom',
        ])->findOrFail($id);
    }

    public function getNextRequisitionNumber(): string
    {
        $last = PurchaseRequisition::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->requisition_number, 4) + 1 : 1;
        return 'REQ-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('requisition_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('requisition_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('requisition_date', '<=', $filters['date_to']);
        }
    }
}
