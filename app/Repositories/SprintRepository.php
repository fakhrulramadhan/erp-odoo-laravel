<?php

namespace App\Repositories;

use App\Models\Sprint;

class SprintRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Sprint());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Sprint::with(['project', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
