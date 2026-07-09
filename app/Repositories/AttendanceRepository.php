<?php

namespace App\Repositories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;

class AttendanceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Attendance());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Attendance::with(['employee', 'shift', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('attendance_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('attendance_date', '<=', $filters['date_to']);
        }
    }
}
