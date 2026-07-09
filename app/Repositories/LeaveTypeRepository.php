<?php

namespace App\Repositories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Builder;

class LeaveTypeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new LeaveType());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = LeaveType::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['is_paid'])) {
            $query->where('is_paid', filter_var($filters['is_paid'], FILTER_VALIDATE_BOOLEAN));
        }
    }
}
