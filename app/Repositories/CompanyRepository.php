<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

class CompanyRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Company());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Company::with(['currency', 'branches']);

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('legal_name', 'like', "%{$search}%");
            });
        }
    }
}
