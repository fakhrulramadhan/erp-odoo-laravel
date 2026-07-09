<?php

namespace App\Repositories;

use App\Models\SavedReport;
use Illuminate\Database\Eloquent\Builder;

class SavedReportRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new SavedReport());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = SavedReport::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['module'])) {
            $query->where('module', $filters['module']);
        }
    }
}
