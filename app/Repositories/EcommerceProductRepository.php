<?php

namespace App\Repositories;

use App\Models\EcommerceProduct;
use Illuminate\Database\Eloquent\Builder;

class EcommerceProductRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EcommerceProduct());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = EcommerceProduct::with(['product', 'images', 'variants', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?EcommerceProduct
    {
        return EcommerceProduct::with([
            'product', 'images', 'variants', 'reviews.customer', 'company', 'creator',
        ])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('slug', 'like', "%{$filters['search']}%")
                  ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$filters['search']}%"));
        }

        if (isset($filters['is_visible'])) {
            $query->where('is_visible', filter_var($filters['is_visible'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN));
        }
    }
}
