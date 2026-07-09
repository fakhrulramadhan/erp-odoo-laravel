<?php

namespace App\Services\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        protected AttendanceRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Attendance
    {
        return $this->repository->findById($id);
    }

    public function checkIn(array $data): Attendance
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = AttendanceStatus::Present;
            $data['check_in'] = now();
            $data['attendance_date'] = $data['attendance_date'] ?? now()->toDateString();
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function checkOut(int $id): Attendance
    {
        return DB::transaction(function () use ($id) {
            $attendance = $this->repository->findById($id);
            $attendance->update([
                'check_out' => now(),
                'updated_by' => auth()->id(),
            ]);
            return $attendance->fresh();
        });
    }

    public function update(int $id, array $data): Attendance
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
