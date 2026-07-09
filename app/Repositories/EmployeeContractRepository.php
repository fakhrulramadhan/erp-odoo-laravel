<?php

namespace App\Repositories;

use App\Models\EmployeeContract;
use Illuminate\Database\Eloquent\Builder;

class EmployeeContractRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EmployeeContract());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = EmployeeContract::with(['employee', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
