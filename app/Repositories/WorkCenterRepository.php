<?php

namespace App\Repositories;

use App\Models\WorkCenter;
use Illuminate\Database\Eloquent\Builder;

class WorkCenterRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new WorkCenter());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = WorkCenter::with(['company', 'warehouse', 'location']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithRelations(int $id): ?WorkCenter
    {
        return WorkCenter::with([
            'company', 'warehouse', 'location', 'equipment', 'operators',
        ])->findOrFail($id);
    }

    public function getNextCode(): string
    {
        $last = WorkCenter::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->code, 3) + 1 : 1;
        return 'WC-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
    }
}
