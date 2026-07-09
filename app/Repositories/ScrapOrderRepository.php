<?php

namespace App\Repositories;

use App\Models\ScrapOrder;
use Illuminate\Database\Eloquent\Builder;

class ScrapOrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new ScrapOrder());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = ScrapOrder::with(['product', 'warehouse', 'location', 'company']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithRelations(int $id): ?ScrapOrder
    {
        return ScrapOrder::with([
            'product', 'warehouse', 'location', 'uom', 'lot', 'source', 'company', 'creator',
        ])->findOrFail($id);
    }

    public function getNextScrapNumber(): string
    {
        $last = ScrapOrder::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->scrap_number, 3) + 1 : 1;
        return 'SC-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('scrap_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['scrap_type'])) {
            $query->where('scrap_type', $filters['scrap_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
