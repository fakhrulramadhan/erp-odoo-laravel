<?php

namespace App\Repositories;

use App\Models\CmsBanner;

class CmsBannerRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsBanner());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsBanner::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
