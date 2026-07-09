<?php

namespace App\Services\CRM;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Repositories\LeadRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        protected LeadRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Lead
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = LeadStatus::New;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Lead
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

    public function qualify(int $id): Lead
    {
        return DB::transaction(function () use ($id) {
            $lead = $this->repository->findById($id);
            $lead->transitionTo(LeadStatus::Qualified);
            return $this->repository->findWithDetails($id);
        });
    }

    public function markAsLost(int $id): Lead
    {
        return DB::transaction(function () use ($id) {
            $lead = $this->repository->findById($id);
            $lead->transitionTo(LeadStatus::Lost);
            return $this->repository->findWithDetails($id);
        });
    }
}
