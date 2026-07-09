<?php

namespace App\Repositories;

use App\Models\Workflow;
use Illuminate\Database\Eloquent\Builder;

class WorkflowRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Workflow());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Workflow::with(['steps', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Workflow
    {
        return Workflow::with(['steps.actions', 'logs', 'company', 'creator'])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }
    }
}
