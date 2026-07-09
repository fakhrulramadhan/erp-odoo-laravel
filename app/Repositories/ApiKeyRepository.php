<?php

namespace App\Repositories;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Builder;

class ApiKeyRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ApiKey());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = ApiKey::with(['user', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }
    }
}
