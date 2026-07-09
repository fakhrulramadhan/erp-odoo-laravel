<?php

namespace App\Repositories;

use App\Models\Interview;
use Illuminate\Database\Eloquent\Builder;

class InterviewRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Interview());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Interview::with(['applicant', 'interviewer', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['applicant_id'])) {
            $query->where('applicant_id', $filters['applicant_id']);
        }

        if (!empty($filters['interviewer_id'])) {
            $query->where('interviewer_id', $filters['interviewer_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('scheduled_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('scheduled_at', '<=', $filters['date_to']);
        }
    }
}
