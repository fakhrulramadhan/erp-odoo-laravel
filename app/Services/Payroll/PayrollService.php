<?php

namespace App\Services\Payroll;

use App\Enums\PayrollStatus;
use App\Models\Payroll;
use App\Repositories\PayrollRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        protected PayrollRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Payroll
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Payroll
    {
        return DB::transaction(function () use ($data) {
            $data['payroll_number'] = $this->repository->getNextPayrollNumber();
            $data['status'] = PayrollStatus::Draft;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Payroll
    {
        return DB::transaction(function () use ($id, $data) {
            $payroll = $this->repository->findById($id);
            if ($payroll->status !== PayrollStatus::Draft) {
                throw new \DomainException('Cannot edit a payroll that is not in draft status.');
            }
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $payroll = $this->repository->findById($id);
            if ($payroll->status !== PayrollStatus::Draft) {
                throw new \DomainException('Cannot delete a payroll that is not in draft status.');
            }
            return $this->repository->delete($id);
        });
    }

    public function process(int $id): Payroll
    {
        return DB::transaction(function () use ($id) {
            $payroll = $this->repository->findById($id);
            $payroll->update(['status' => PayrollStatus::Processing, 'updated_by' => auth()->id()]);
            // TODO: Generate payslips for employees
            return $this->repository->findWithDetails($id);
        });
    }

    public function confirm(int $id): Payroll
    {
        return DB::transaction(function () use ($id) {
            $payroll = $this->repository->findById($id);
            $payroll->update(['status' => PayrollStatus::Confirmed, 'updated_by' => auth()->id()]);
            return $this->repository->findWithDetails($id);
        });
    }

    public function approve(int $id): Payroll
    {
        return DB::transaction(function () use ($id) {
            $payroll = $this->repository->findById($id);
            $payroll->update([
                'status' => PayrollStatus::Approved,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);
            return $this->repository->findWithDetails($id);
        });
    }

    public function cancel(int $id): Payroll
    {
        return DB::transaction(function () use ($id) {
            $payroll = $this->repository->findById($id);
            $payroll->update(['status' => PayrollStatus::Cancelled, 'updated_by' => auth()->id()]);
            return $this->repository->findWithDetails($id);
        });
    }
}
