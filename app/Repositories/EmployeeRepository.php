<?php

namespace App\Repositories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Employee());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Employee::with(['user', 'department', 'position', 'branch', 'company', 'manager', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Employee
    {
        return Employee::with([
            'user', 'department', 'position', 'branch', 'company', 'manager',
            'contracts', 'documents', 'attendances', 'leaveRequests',
            'leaveBalances', 'salaries', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['position_id'])) {
            $query->where('position_id', $filters['position_id']);
        }

        if (!empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (!empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }
    }
}
