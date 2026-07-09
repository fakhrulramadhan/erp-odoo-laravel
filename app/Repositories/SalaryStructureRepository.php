<?php

namespace App\Repositories;

use App\Models\SalaryStructure;
use Illuminate\Database\Eloquent\Builder;

class SalaryStructureRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new SalaryStructure());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = SalaryStructure::with(['lines.component', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?SalaryStructure
    {
        return SalaryStructure::with(['lines.component', 'company', 'creator'])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
    }
}
