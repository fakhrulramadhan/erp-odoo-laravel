<?php

namespace App\Repositories;

use App\Models\StockQuant;
use Illuminate\Database\Eloquent\Builder;

class StockQuantRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new StockQuant());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = StockQuant::with(['product', 'location.warehouse', 'lot']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function getForProduct(int $productId, ?int $warehouseId = null)
    {
        return StockQuant::with(['location.warehouse', 'lot'])
            ->where('product_id', $productId)
            ->when($warehouseId, fn($q) => $q->whereHas('location', fn($lq) => $lq->where('warehouse_id', $warehouseId)))
            ->get();
    }

    public function getTotalOnHand(int $productId): float
    {
        return (float) StockQuant::where('product_id', $productId)->sum('quantity');
    }

    public function getLowStockProducts(?int $warehouseId = null)
    {
        return StockQuant::with(['product', 'location'])
            ->where('quantity', '<=', 0)
            ->when($warehouseId, fn($q) => $q->whereHas('location', fn($lq) => $lq->where('warehouse_id', $warehouseId)))
            ->get();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('product', function ($pq) use ($search) {
                $pq->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->whereHas('location', fn($q) => $q->where('warehouse_id', $filters['warehouse_id']));
        }

        if (($filters['low_stock'] ?? false)) {
            $query->whereColumn('available_quantity', '<=', 0);
        }
    }
}
