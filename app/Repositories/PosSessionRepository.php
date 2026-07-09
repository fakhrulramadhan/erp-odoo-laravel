<?php

namespace App\Repositories;

use App\Models\PosSession;
use Illuminate\Database\Eloquent\Builder;

class PosSessionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new PosSession());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = PosSession::with(['user', 'warehouse', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?PosSession
    {
        return PosSession::with([
            'user', 'warehouse', 'company', 'branch',
            'orders.lines.product', 'orders.payments.paymentMethod', 'creator',
        ])->findOrFail($id);
    }

    public function getNextSessionNumber(): string
    {
        $last = PosSession::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->session_number, 3) + 1 : 1;
        return 'PS-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
    }
}
