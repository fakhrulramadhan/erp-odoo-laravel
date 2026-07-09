<?php

namespace App\Services\Workflow;

use App\Enums\ApprovalStatus;
use App\Models\Approval;
use App\Repositories\ApprovalRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(
        protected ApprovalRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Approval
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Approval
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = ApprovalStatus::Pending;
            $data['requested_by'] = auth()->id();
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function approve(int $id, ?string $comment = null): Approval
    {
        return DB::transaction(function () use ($id, $comment) {
            return $this->repository->update($id, [
                'status' => ApprovalStatus::Approved,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'comment' => $comment,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function reject(int $id, string $comment): Approval
    {
        return DB::transaction(function () use ($id, $comment) {
            return $this->repository->update($id, [
                'status' => ApprovalStatus::Rejected,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'comment' => $comment,
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
