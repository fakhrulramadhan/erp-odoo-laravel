<?php

namespace App\Repositories;

use App\Models\LeaveBalance;
use Illuminate\Database\Eloquent\Builder;

class LeaveBalanceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new LeaveBalance());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = LeaveBalance::with(['employee', 'leaveType', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }
    }
}
