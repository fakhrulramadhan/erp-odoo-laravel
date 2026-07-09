<?php

namespace App\Repositories;

use App\Models\CmsPage;
use Illuminate\Database\Eloquent\Builder;

class CmsPageRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsPage());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsPage::with(['author', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('slug', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
