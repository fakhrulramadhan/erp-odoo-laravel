<?php

namespace App\Services\Leave;

use App\Models\LeaveBalance;
use App\Repositories\LeaveBalanceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeaveBalanceService
{
    public function __construct(
        protected LeaveBalanceRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): LeaveBalance
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): LeaveBalance
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): LeaveBalance
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function initializeYear(int $year, int $companyId): int
    {
        return DB::transaction(function () use ($year, $companyId) {
            // Bulk initialize leave balances for active employees
            $employees = \App\Models\Employee::where('company_id', $companyId)
                ->where('status', \App\Enums\EmployeeStatus::Active)
                ->get();

            $leaveTypes = \App\Models\LeaveType::where('company_id', $companyId)->get();
            $count = 0;

            foreach ($employees as $employee) {
                foreach ($leaveTypes as $leaveType) {
                    LeaveBalance::firstOrCreate(
                        ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $year],
                        ['entitled' => $leaveType->default_days ?? 0, 'used' => 0, 'company_id' => $companyId, 'created_by' => auth()->id()]
                    );
                    $count++;
                }
            }

            return $count;
        });
    }
}
