<?php

namespace App\Services\Payroll;

use App\Enums\PayslipStatus;
use App\Models\Payslip;
use App\Repositories\PayslipRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PayslipService
{
    public function __construct(
        protected PayslipRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Payslip
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Payslip
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['status'] = PayslipStatus::Draft;
            $data['created_by'] = auth()->id();
            $payslip = $this->repository->create($data);

            foreach ($lines as $line) {
                $payslip->lines()->create($line);
            }

            return $this->repository->findWithDetails($payslip->id);
        });
    }

    public function confirm(int $id): Payslip
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => PayslipStatus::Confirmed,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function pay(int $id): Payslip
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => PayslipStatus::Paid,
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
