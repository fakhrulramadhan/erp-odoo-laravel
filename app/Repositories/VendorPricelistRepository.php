<?php

namespace App\Repositories;

use App\Models\VendorPricelist;
use Illuminate\Database\Eloquent\Builder;

class VendorPricelistRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new VendorPricelist());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = VendorPricelist::with(['vendor', 'product', 'uom', 'currency']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function getBestPrice(int $productId, float $quantity = 1): ?VendorPricelist
    {
        return VendorPricelist::active()->valid()
            ->where('product_id', $productId)
            ->where('min_quantity', '<=', $quantity)
            ->orderBy('price', 'asc')
            ->first();
    }

    public function getPricesForProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return VendorPricelist::with('vendor', 'uom')
            ->active()->valid()
            ->where('product_id', $productId)
            ->orderBy('price', 'asc')
            ->get();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('vendor', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['active_only'])) {
            $query->active()->valid();
        }
    }
}
