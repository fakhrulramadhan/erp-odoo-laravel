<?php

namespace App\Repositories;

use App\Models\AttendanceCorrection;

class AttendanceCorrectionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AttendanceCorrection());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = AttendanceCorrection::with(['attendance', 'employee', 'approvedBy', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
