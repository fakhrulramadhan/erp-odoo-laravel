<?php

namespace App\Services\Expense;

use App\Enums\ExpenseClaimStatus;
use App\Models\ExpenseClaim;
use App\Repositories\ExpenseClaimRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseClaimService
{
    public function __construct(
        protected ExpenseClaimRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): ExpenseClaim
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): ExpenseClaim
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['claim_number'] = $this->repository->getNextClaimNumber();
            $data['status'] = ExpenseClaimStatus::Draft;
            $data['employee_id'] = $data['employee_id'] ?? auth()->id();
            $data['created_by'] = auth()->id();
            $claim = $this->repository->create($data);

            foreach ($lines as $line) {
                $claim->lines()->create($line);
            }

            return $this->repository->findWithDetails($claim->id);
        });
    }

    public function update(int $id, array $data): ExpenseClaim
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function submit(int $id): ExpenseClaim
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => ExpenseClaimStatus::Submitted,
                'submitted_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function approve(int $id): ExpenseClaim
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => ExpenseClaimStatus::Approved,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function reject(int $id, string $reason = ''): ExpenseClaim
    {
        return DB::transaction(function () use ($id, $reason) {
            return $this->repository->update($id, [
                'status' => ExpenseClaimStatus::Rejected,
                'rejection_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function markPaid(int $id): ExpenseClaim
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => ExpenseClaimStatus::Paid,
                'paid_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
