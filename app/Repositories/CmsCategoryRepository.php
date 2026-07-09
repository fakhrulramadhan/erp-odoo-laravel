<?php

namespace App\Repositories;

use App\Models\CmsCategory;

class CmsCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsCategory());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsCategory::with(['parent', 'children', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
