<?php

namespace App\Repositories;

use App\Models\Payroll;
use Illuminate\Database\Eloquent\Builder;

class PayrollRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Payroll());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Payroll::with(['period', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Payroll
    {
        return Payroll::with([
            'period', 'company', 'branch',
            'payslips.employee', 'payslips.lines.component',
            'creator',
        ])->findOrFail($id);
    }

    public function getNextPayrollNumber(): string
    {
        $last = Payroll::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->payroll_number, 3) + 1 : 1;
        return 'PR-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('payroll_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['period_id'])) {
            $query->where('payroll_period_id', $filters['period_id']);
        }
    }
}
