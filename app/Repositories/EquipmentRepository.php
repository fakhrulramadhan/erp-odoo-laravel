<?php

namespace App\Repositories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Builder;

class EquipmentRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Equipment());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Equipment::with(['company', 'workCenter', 'warehouse', 'location']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithRelations(int $id): ?Equipment
    {
        return Equipment::with([
            'company', 'workCenter', 'warehouse', 'location', 'asset', 'creator',
        ])->findOrFail($id);
    }

    public function getNextCode(): string
    {
        $last = Equipment::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->code, 3) + 1 : 1;
        return 'EQ-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }
}
