<?php

namespace App\Services\Project;

use App\Models\Sprint;
use App\Repositories\SprintRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SprintService
{
    public function __construct(
        protected SprintRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Sprint
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Sprint
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Sprint
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

    public function start(int $id): Sprint
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'active',
                'started_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function complete(int $id): Sprint
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'completed',
                'completed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
