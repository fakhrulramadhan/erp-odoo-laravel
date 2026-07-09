<?php

namespace App\Services\HRM;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use App\Repositories\EmployeeRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Employee
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? EmployeeStatus::Active;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Employee
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

    public function terminate(int $id, ?string $terminationDate = null): Employee
    {
        return DB::transaction(function () use ($id, $terminationDate) {
            return $this->repository->update($id, [
                'status' => EmployeeStatus::Terminated,
                'termination_date' => $terminationDate ?? now()->toDateString(),
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
