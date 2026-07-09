<?php

namespace App\Repositories;

use App\Models\PromoCode;
use Illuminate\Database\Eloquent\Builder;

class PromoCodeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PromoCode());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PromoCode::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('code', 'like', "%{$filters['search']}%")
                  ->orWhere('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
    }
}
