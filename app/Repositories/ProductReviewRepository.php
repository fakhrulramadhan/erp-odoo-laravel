<?php

namespace App\Repositories;

use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Builder;

class ProductReviewRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ProductReview());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = ProductReview::with(['ecommerceProduct', 'customer', 'ecommerceOrder', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['ecommerce_product_id'])) {
            $query->where('ecommerce_product_id', $filters['ecommerce_product_id']);
        }

        if (isset($filters['is_approved'])) {
            $query->where('is_approved', filter_var($filters['is_approved'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }
    }
}
