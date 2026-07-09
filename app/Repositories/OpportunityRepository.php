<?php

namespace App\Repositories;

use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Builder;

class OpportunityRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Opportunity());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Opportunity::with(['customer', 'lead', 'company', 'branch', 'assignedUser', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Opportunity
    {
        return Opportunity::with([
            'customer', 'lead', 'company', 'branch', 'assignedUser', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('expected_close_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('expected_close_date', '<=', $filters['date_to']);
        }
    }
}
