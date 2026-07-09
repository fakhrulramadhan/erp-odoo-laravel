<?php

namespace App\Repositories;

use App\Models\EmployeeDocument;

class EmployeeDocumentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EmployeeDocument());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = EmployeeDocument::with(['employee', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
