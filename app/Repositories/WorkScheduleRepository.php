<?php

namespace App\Repositories;

use App\Models\WorkSchedule;

class WorkScheduleRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new WorkSchedule());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = WorkSchedule::with(['company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
