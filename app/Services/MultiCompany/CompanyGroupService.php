<?php

namespace App\Services\MultiCompany;

use App\Models\CompanyGroup;
use App\Repositories\CompanyGroupRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyGroupService
{
    public function __construct(
        protected CompanyGroupRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): CompanyGroup
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): CompanyGroup
    {
        return DB::transaction(function () use ($data) {
            $companies = $data['companies'] ?? [];
            unset($data['companies']);

            $data['created_by'] = auth()->id();
            $group = $this->repository->create($data);

            if (!empty($companies)) {
                $group->companies()->sync($companies);
            }

            return $group->fresh(['companies']);
        });
    }

    public function update(int $id, array $data): CompanyGroup
    {
        return DB::transaction(function () use ($id, $data) {
            $companies = $data['companies'] ?? null;
            unset($data['companies']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($companies !== null) {
                $group = $this->repository->findById($id);
                $group->companies()->sync($companies);
            }

            return $this->repository->findById($id)->fresh(['companies']);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
