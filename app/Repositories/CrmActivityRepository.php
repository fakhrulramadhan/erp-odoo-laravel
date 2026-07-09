<?php

namespace App\Repositories;

use App\Models\CrmActivity;
use Illuminate\Database\Eloquent\Builder;

class CrmActivityRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CrmActivity());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CrmActivity::with(['activityable', 'user', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('due_date', '<=', $filters['date_to']);
        }
    }
}
