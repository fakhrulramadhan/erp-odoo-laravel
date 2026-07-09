<?php

namespace App\Repositories;

use App\Models\Sla;

class SlaRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Sla());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Sla::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
