<?php

namespace App\Repositories;

use App\Models\JobVacancy;
use Illuminate\Database\Eloquent\Builder;

class JobVacancyRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new JobVacancy());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = JobVacancy::with(['department', 'position', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
