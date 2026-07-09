<?php

namespace App\Repositories;

use App\Models\Routing;
use Illuminate\Database\Eloquent\Builder;

class RoutingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Routing());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Routing::with(['company', 'product', 'operations.workCenter']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithOperations(int $id): ?Routing
    {
        return Routing::with([
            'company', 'product', 'operations.workCenter', 'creator',
        ])->findOrFail($id);
    }

    public function getNextRoutingNumber(): string
    {
        $last = Routing::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->routing_number, 3) + 1 : 1;
        return 'RT-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('routing_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }
    }
}
