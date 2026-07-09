<?php

namespace App\Repositories;

use App\Models\CmsMenu;

class CmsMenuRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new CmsMenu());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = CmsMenu::with(['parent', 'children', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
