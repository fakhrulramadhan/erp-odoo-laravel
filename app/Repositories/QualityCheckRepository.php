<?php

namespace App\Repositories;

use App\Models\QualityCheck;
use Illuminate\Database\Eloquent\Builder;

class QualityCheckRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new QualityCheck());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = QualityCheck::with(['product', 'inspector', 'qualityControlPoint', 'company']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?QualityCheck
    {
        return QualityCheck::with([
            'product', 'uom', 'inspector', 'qualityControlPoint',
            'quarantineLocation', 'lines', 'source',
            'approver', 'creator', 'company',
        ])->findOrFail($id);
    }

    public function getNextCheckNumber(): string
    {
        $last = QualityCheck::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->check_number, 3) + 1 : 1;
        return 'QC-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where('check_number', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['inspection_type'])) {
            $query->where('inspection_type', $filters['inspection_type']);
        }
    }
}
