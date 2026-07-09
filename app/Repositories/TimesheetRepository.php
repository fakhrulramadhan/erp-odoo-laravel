<?php

namespace App\Repositories;

use App\Models\Timesheet;
use Illuminate\Database\Eloquent\Builder;

class TimesheetRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Timesheet());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Timesheet::with(['employee', 'project', 'task', 'approvedBy', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('work_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('work_date', '<=', $filters['date_to']);
        }
    }
}
