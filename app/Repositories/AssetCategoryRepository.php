<?php

namespace App\Repositories;

use App\Models\AssetCategory;
use Illuminate\Database\Eloquent\Builder;

class AssetCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new AssetCategory());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = AssetCategory::with(['parent', 'company']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithRelations(int $id): ?AssetCategory
    {
        return AssetCategory::with([
            'parent', 'children', 'company',
            'depreciationExpenseAccount', 'accumulatedDepreciationAccount',
            'assetAccount', 'disposalAccount', 'creator',
        ])->findOrFail($id);
    }

    public function getNextCode(): string
    {
        $last = AssetCategory::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->code, 3) + 1 : 1;
        return 'AC-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
    }
}
