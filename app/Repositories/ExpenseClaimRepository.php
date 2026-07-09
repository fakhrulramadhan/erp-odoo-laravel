<?php

namespace App\Repositories;

use App\Models\ExpenseClaim;
use Illuminate\Database\Eloquent\Builder;

class ExpenseClaimRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ExpenseClaim());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = ExpenseClaim::with(['employee', 'approvedBy', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?ExpenseClaim
    {
        return ExpenseClaim::with([
            'employee', 'approvedBy', 'company', 'branch', 'lines', 'creator',
        ])->findOrFail($id);
    }

    public function getNextClaimNumber(): string
    {
        $last = ExpenseClaim::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->claim_number, 3) + 1 : 1;
        return 'EC-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('claim_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('claim_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('claim_date', '<=', $filters['date_to']);
        }
    }
}
