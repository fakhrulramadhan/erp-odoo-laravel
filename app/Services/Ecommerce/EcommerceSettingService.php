<?php

namespace App\Services\Ecommerce;

use App\Models\EcommerceSetting;
use App\Repositories\EcommerceSettingRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EcommerceSettingService
{
    public function __construct(
        protected EcommerceSettingRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): EcommerceSetting
    {
        return $this->repository->findById($id);
    }

    public function findByCompany(int $companyId): ?EcommerceSetting
    {
        return $this->repository->findByCompany($companyId);
    }

    public function createOrUpdate(array $data): EcommerceSetting
    {
        return DB::transaction(function () use ($data) {
            $existing = $this->repository->findByCompany($data['company_id']);
            if ($existing) {
                $data['updated_by'] = auth()->id();
                return $this->repository->update($existing->id, $data);
            }
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): EcommerceSetting
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }
}
