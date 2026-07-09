<?php

namespace App\Repositories;

use App\Models\SupplierQuotation;
use Illuminate\Database\Eloquent\Builder;

class SupplierQuotationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new SupplierQuotation());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = SupplierQuotation::with(['vendor', 'company', 'currency', 'purchaseOrder', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithLines(int $id): ?SupplierQuotation
    {
        return SupplierQuotation::with([
            'vendor', 'company', 'currency',
            'purchaseOrder', 'lines.product', 'lines.uom',
            'creator',
        ])->findOrFail($id);
    }

    public function getNextQuotationNumber(): string
    {
        $last = SupplierQuotation::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->quotation_number, 4) + 1 : 1;
        return 'RFQ-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($vq) => $vq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('quotation_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('quotation_date', '<=', $filters['date_to']);
        }
    }
}
