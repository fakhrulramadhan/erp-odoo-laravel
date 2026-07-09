<?php

namespace App\Repositories;

use App\Models\CompanyGroup;

class CompanyGroupRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CompanyGroup());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CompanyGroup::with(['parent', 'children', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
