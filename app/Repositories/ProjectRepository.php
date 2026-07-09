<?php

namespace App\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;

class ProjectRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Project());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Project::with(['company', 'branch', 'manager', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Project
    {
        return Project::with([
            'company', 'branch', 'manager',
            'members.user', 'milestones', 'sprints',
            'tasks', 'timesheets', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['manager_id'])) {
            $query->where('manager_id', $filters['manager_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('start_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('end_date', '<=', $filters['date_to']);
        }
    }
}
