<?php

namespace App\Services\CRM;

use App\Models\CrmActivity;
use App\Repositories\CrmActivityRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CrmActivityService
{
    public function __construct(
        protected CrmActivityRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): CrmActivity
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): CrmActivity
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = $data['user_id'] ?? auth()->id();
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): CrmActivity
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

    public function complete(int $id): CrmActivity
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'is_done' => true,
                'completed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
