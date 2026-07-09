<?php

namespace App\Services\Audit;

use App\Models\AuditTrail;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditService
{
    public function list(array $filters = [], ?int $perPage = 20): LengthAwarePaginator
    {
        $query = AuditTrail::with('user');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function getForModel(string $type, int $id)
    {
        return AuditTrail::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }
}
