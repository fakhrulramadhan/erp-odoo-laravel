<?php

namespace App\Repositories;

use App\Models\StockPicking;
use Illuminate\Database\Eloquent\Builder;

class StockPickingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new StockPicking());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = StockPicking::with(['sourceLocation', 'destinationLocation', 'vendor', 'customer', 'purchaseOrder', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithMoves(int $id): ?StockPicking
    {
        return StockPicking::with([
            'sourceLocation', 'destinationLocation', 'vendor', 'customer',
            'purchaseOrder', 'moves.product', 'moves.uom', 'moves.lot',
            'moves.sourceLocation', 'moves.destinationLocation', 'creator',
        ])->findOrFail($id);
    }

    public function getNextPickingNumber(string $type): string
    {
        $prefix = match ($type) {
            'incoming' => 'IN',
            'outgoing' => 'OUT',
            'internal' => 'INT',
            'dropship' => 'DS',
            default => 'STK',
        };

        $last = StockPicking::where('picking_type', $type)->withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->picking_number, strlen($prefix) + 1) + 1 : 1;
        return $prefix . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('picking_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['picking_type'])) {
            $query->where('picking_type', $filters['picking_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('scheduled_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('scheduled_date', '<=', $filters['date_to']);
        }
    }
}
