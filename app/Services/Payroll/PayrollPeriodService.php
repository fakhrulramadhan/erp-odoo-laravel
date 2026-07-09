<?php

namespace App\Services\Payroll;

use App\Models\PayrollPeriod;
use App\Repositories\PayrollPeriodRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PayrollPeriodService
{
    public function __construct(
        protected PayrollPeriodRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): PayrollPeriod
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): PayrollPeriod
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): PayrollPeriod
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
}
