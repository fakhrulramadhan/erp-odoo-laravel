<?php

namespace App\Services\CRM;

use App\Models\Opportunity;
use App\Repositories\OpportunityRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OpportunityService
{
    public function __construct(
        protected OpportunityRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Opportunity
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Opportunity
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Opportunity
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function updateStage(int $id, string $stage): Opportunity
    {
        return DB::transaction(function () use ($id, $stage) {
            $this->repository->update($id, ['stage' => $stage, 'updated_by' => auth()->id()]);
            return $this->repository->findWithDetails($id);
        });
    }
}
