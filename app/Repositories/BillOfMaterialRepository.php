<?php

namespace App\Repositories;

use App\Models\BillOfMaterial;
use Illuminate\Database\Eloquent\Builder;

class BillOfMaterialRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new BillOfMaterial());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = BillOfMaterial::with(['product', 'uom', 'workCenter', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?BillOfMaterial
    {
        return BillOfMaterial::with([
            'product', 'uom', 'workCenter', 'company',
            'lines.product', 'lines.uom', 'lines.alternatives',
            'revisions', 'approver', 'creator',
        ])->findOrFail($id);
    }

    public function getNextBomNumber(): string
    {
        $last = BillOfMaterial::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->bom_number, 4) + 1 : 1;
        return 'BOM-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function getDefaultForProduct(int $productId): ?BillOfMaterial
    {
        return BillOfMaterial::where('product_id', $productId)
            ->where('is_default', true)
            ->active()
            ->first();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('bom_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
    }
}
