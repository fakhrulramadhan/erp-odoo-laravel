<?php

namespace App\Services\Project;

use App\Models\Timesheet;
use App\Repositories\TimesheetRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TimesheetService
{
    public function __construct(
        protected TimesheetRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Timesheet
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Timesheet
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? 'draft';
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Timesheet
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function approve(int $id): Timesheet
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
