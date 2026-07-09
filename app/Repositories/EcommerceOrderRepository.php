<?php

namespace App\Repositories;

use App\Models\EcommerceOrder;
use Illuminate\Database\Eloquent\Builder;

class EcommerceOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new EcommerceOrder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = EcommerceOrder::with(['customer', 'shippingMethod', 'promoCode', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?EcommerceOrder
    {
        return EcommerceOrder::with([
            'customer', 'shippingMethod', 'promoCode', 'company',
            'lines.ecommerceProduct', 'lines.variant', 'creator',
        ])->findOrFail($id);
    }

    public function getNextOrderNumber(): string
    {
        $last = EcommerceOrder::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->order_number, 3) + 1 : 1;
        return 'EC-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('order_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }
    }
}
