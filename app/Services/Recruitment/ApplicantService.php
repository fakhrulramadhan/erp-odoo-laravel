<?php

namespace App\Services\Recruitment;

use App\Models\Applicant;
use App\Repositories\ApplicantRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApplicantService
{
    public function __construct(
        protected ApplicantRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Applicant
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Applicant
    {
        return DB::transaction(function () use ($data) {
            $data['stage'] = $data['stage'] ?? \App\Enums\ApplicantStage::Applied;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Applicant
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function updateStage(int $id, string $stage): Applicant
    {
        return DB::transaction(function () use ($id, $stage) {
            return $this->repository->update($id, [
                'stage' => $stage,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
