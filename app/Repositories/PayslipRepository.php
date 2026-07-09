<?php

namespace App\Repositories;

use App\Models\Payslip;
use Illuminate\Database\Eloquent\Builder;

class PayslipRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Payslip());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Payslip::with(['employee', 'payroll', 'lines.component', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Payslip
    {
        return Payslip::with([
            'employee', 'payroll', 'lines.component', 'company', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['payroll_id'])) {
            $query->where('payroll_id', $filters['payroll_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
