<?php

namespace App\Repositories;

use App\Models\TicketCategory;
use Illuminate\Database\Eloquent\Builder;

class TicketCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new TicketCategory());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = TicketCategory::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
    }
}
