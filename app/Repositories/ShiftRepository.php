<?php

namespace App\Repositories;

use App\Models\Shift;

class ShiftRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Shift());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Shift::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
