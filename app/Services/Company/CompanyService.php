<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    public function __construct(
        protected CompanyRepository $companyRepository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15)
    {
        return $this->companyRepository->getWithRelations($filters, $perPage);
    }

    public function create(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            return $this->companyRepository->create($data);
        });
    }

    public function update(int $id, array $data): Company
    {
        return $this->companyRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->companyRepository->delete($id);
    }

    public function find(int $id): Company
    {
        return $this->companyRepository->findById($id);
    }
}
