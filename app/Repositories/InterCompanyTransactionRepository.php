<?php

namespace App\Repositories;

use App\Models\InterCompanyTransaction;
use Illuminate\Database\Eloquent\Builder;

class InterCompanyTransactionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new InterCompanyTransaction());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = InterCompanyTransaction::with(['fromCompany', 'toCompany', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['from_company_id'])) {
            $query->where('from_company_id', $filters['from_company_id']);
        }

        if (!empty($filters['to_company_id'])) {
            $query->where('to_company_id', $filters['to_company_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
