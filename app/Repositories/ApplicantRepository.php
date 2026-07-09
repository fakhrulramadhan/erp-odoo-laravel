<?php

namespace App\Repositories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Builder;

class ApplicantRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Applicant());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Applicant::with(['vacancy', 'company', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Applicant
    {
        return Applicant::with(['vacancy', 'interviews', 'company', 'creator'])->findOrFail($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (!empty($filters['vacancy_id'])) {
            $query->where('vacancy_id', $filters['vacancy_id']);
        }
    }
}
