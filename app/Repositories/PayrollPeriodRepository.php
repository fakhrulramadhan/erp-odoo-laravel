<?php

namespace App\Repositories;

use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Builder;

class PayrollPeriodRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PayrollPeriod());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PayrollPeriod::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }
    }
}
