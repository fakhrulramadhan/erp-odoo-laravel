<?php

namespace App\Repositories;

use App\Models\EmployeeSalary;

class EmployeeSalaryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EmployeeSalary());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = EmployeeSalary::with(['employee', 'salaryStructure', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
