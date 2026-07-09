<?php

namespace App\Repositories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Builder;

class AssetRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Asset());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Asset::with(['company', 'branch', 'category', 'warehouse', 'location', 'assignee', 'department']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithRelations(int $id): ?Asset
    {
        return Asset::with([
            'company', 'branch', 'category', 'product', 'warehouse', 'location',
            'assignee', 'department', 'vendor', 'creator',
            'depreciations', 'transfers',
        ])->findOrFail($id);
    }

    public function getNextAssetNumber(): string
    {
        $last = Asset::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->asset_number, 3) + 1 : 1;
        return 'FA-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
    }
}
