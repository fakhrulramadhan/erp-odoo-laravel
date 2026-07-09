<?php

namespace App\Services\Payroll;

use App\Models\SalaryStructure;
use App\Repositories\SalaryStructureRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SalaryStructureService
{
    public function __construct(
        protected SalaryStructureRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): SalaryStructure
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): SalaryStructure
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['created_by'] = auth()->id();
            $structure = $this->repository->create($data);

            foreach ($lines as $line) {
                $structure->lines()->create($line);
            }

            return $this->repository->findWithLines($structure->id);
        });
    }

    public function update(int $id, array $data): SalaryStructure
    {
        return DB::transaction(function () use ($id, $data) {
            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($lines !== null) {
                $structure = $this->repository->findById($id);
                $structure->lines()->delete();
                foreach ($lines as $line) {
                    $structure->lines()->create($line);
                }
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
