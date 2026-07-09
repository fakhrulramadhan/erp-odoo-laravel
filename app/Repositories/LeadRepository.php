<?php

namespace App\Repositories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;

class LeadRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Lead());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Lead::with(['customer', 'company', 'branch', 'assignedUser', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Lead
    {
        return Lead::with([
            'customer', 'company', 'branch', 'assignedUser',
            'opportunities', 'activities', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
    }
}
