<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Task());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Task::with(['project', 'milestone', 'sprint', 'assignedTo', 'parent', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Task
    {
        return Task::with([
            'project', 'milestone', 'sprint', 'assignedTo', 'parent',
            'children', 'comments.user', 'attachments.uploader', 'timesheets', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('task_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['sprint_id'])) {
            $query->where('sprint_id', $filters['sprint_id']);
        }
    }
}
