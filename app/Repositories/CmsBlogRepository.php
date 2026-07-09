<?php

namespace App\Repositories;

use App\Models\CmsBlog;
use Illuminate\Database\Eloquent\Builder;

class CmsBlogRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsBlog());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsBlog::with(['category', 'author', 'tags', 'company', 'creator']);
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

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
    }
}
