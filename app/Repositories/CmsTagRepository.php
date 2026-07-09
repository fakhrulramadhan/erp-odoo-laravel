<?php

namespace App\Repositories;

use App\Models\CmsTag;

class CmsTagRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsTag());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsTag::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
