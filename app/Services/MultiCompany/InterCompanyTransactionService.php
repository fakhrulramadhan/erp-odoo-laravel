<?php

namespace App\Services\MultiCompany;

use App\Models\InterCompanyTransaction;
use App\Repositories\InterCompanyTransactionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InterCompanyTransactionService
{
    public function __construct(
        protected InterCompanyTransactionRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): InterCompanyTransaction
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): InterCompanyTransaction
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? 'pending';
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): InterCompanyTransaction
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function approve(int $id): InterCompanyTransaction
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function reject(int $id): InterCompanyTransaction
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
