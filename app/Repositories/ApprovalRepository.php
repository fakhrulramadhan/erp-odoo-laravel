<?php

namespace App\Repositories;

use App\Models\Approval;
use Illuminate\Database\Eloquent\Builder;

class ApprovalRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Approval());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Approval::with(['approvalable', 'requestedBy', 'approvedBy', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['requested_by'])) {
            $query->where('requested_by', $filters['requested_by']);
        }

        if (!empty($filters['approved_by'])) {
            $query->where('approved_by', $filters['approved_by']);
        }
    }
}
