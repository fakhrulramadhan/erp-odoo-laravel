<?php

namespace App\Repositories;

use App\Models\ShippingMethod;

class ShippingMethodRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ShippingMethod());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = ShippingMethod::with(['company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }
}
