<?php

namespace App\Services\Leave;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveRequest;
use App\Repositories\LeaveRequestRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeaveRequestService
{
    public function __construct(
        protected LeaveRequestRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): LeaveRequest
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): LeaveRequest
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = LeaveRequestStatus::Pending;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($id, $data) {
            $request = $this->repository->findById($id);
            if ($request->status !== LeaveRequestStatus::Pending) {
                throw new \DomainException('Cannot edit a leave request that is not pending.');
            }
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function approve(int $id): LeaveRequest
    {
        return DB::transaction(function () use ($id) {
            $request = $this->repository->findById($id);
            $request->update([
                'status' => LeaveRequestStatus::Approved,
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Deduct leave balance
            $balance = \App\Models\LeaveBalance::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($balance) {
                $balance->increment('used', $request->total_days);
            }

            return $request->fresh();
        });
    }

    public function reject(int $id, string $reason): LeaveRequest
    {
        return DB::transaction(function () use ($id, $reason) {
            return $this->repository->update($id, [
                'status' => LeaveRequestStatus::Rejected,
                'rejection_reason' => $reason,
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function cancel(int $id): LeaveRequest
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => LeaveRequestStatus::Cancelled,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
