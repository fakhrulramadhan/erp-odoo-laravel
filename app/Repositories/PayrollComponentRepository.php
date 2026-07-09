<?php

namespace App\Repositories;

use App\Models\PayrollComponent;
use Illuminate\Database\Eloquent\Builder;

class PayrollComponentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PayrollComponent());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PayrollComponent::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
    }
}
