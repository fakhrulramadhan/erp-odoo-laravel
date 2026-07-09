<?php

namespace App\Repositories;

use App\Models\Dashboard;
use Illuminate\Database\Eloquent\Builder;

class DashboardRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Dashboard());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Dashboard::with(['widgets', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithWidgets(int $id): ?Dashboard
    {
        return Dashboard::with(['widgets', 'company', 'creator'])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
    }
}
