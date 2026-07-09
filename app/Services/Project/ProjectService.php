<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Repositories\ProjectRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        protected ProjectRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Project
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Project
    {
        return DB::transaction(function () use ($data) {
            $members = $data['members'] ?? [];
            unset($data['members']);

            $data['status'] = $data['status'] ?? \App\Enums\ProjectStatus::Planning;
            $data['created_by'] = auth()->id();
            $project = $this->repository->create($data);

            foreach ($members as $member) {
                $project->members()->create($member);
            }

            return $this->repository->findWithDetails($project->id);
        });
    }

    public function update(int $id, array $data): Project
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

    public function updateStatus(int $id, string $status): Project
    {
        return DB::transaction(function () use ($id, $status) {
            return $this->repository->update($id, [
                'status' => $status,
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
