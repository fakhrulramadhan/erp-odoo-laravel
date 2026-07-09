<?php

namespace App\Repositories;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;

class LeaveRequestRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new LeaveRequest());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'approvedBy', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }
    }
}
