<?php

namespace App\Repositories;

use App\Models\Milestone;

class MilestoneRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Milestone());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Milestone::with(['project', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
