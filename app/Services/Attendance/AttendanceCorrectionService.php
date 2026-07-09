<?php

namespace App\Services\Attendance;

use App\Models\AttendanceCorrection;
use App\Repositories\AttendanceCorrectionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionService
{
    public function __construct(
        protected AttendanceCorrectionRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): AttendanceCorrection
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): AttendanceCorrection
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function approve(int $id): AttendanceCorrection
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'is_approved' => true,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function reject(int $id, string $reason): AttendanceCorrection
    {
        return DB::transaction(function () use ($id, $reason) {
            return $this->repository->update($id, [
                'is_approved' => false,
                'rejection_reason' => $reason,
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
