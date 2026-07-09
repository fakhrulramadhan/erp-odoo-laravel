<?php

namespace App\Services\Manufacturing;

use App\Models\Equipment;
use App\Repositories\EquipmentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class EquipmentService
{
    public function __construct(
        protected EquipmentRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Equipment
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(array $data): Equipment
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = $data['code'] ?? $this->repository->getNextCode();
            $data['created_by'] = auth()->id();

            $equipment = $this->repository->create($data);
            return $this->repository->findWithRelations($equipment->id);
        });
    }

    public function update(int $id, array $data): Equipment
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);
            return $this->repository->findWithRelations($id);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
