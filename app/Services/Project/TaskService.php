<?php

namespace App\Services\Project;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskService
{
    public function __construct(
        protected TaskRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Task
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? \App\Enums\TaskStatus::Todo;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Task
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

    public function updateStatus(int $id, string $status): Task
    {
        return DB::transaction(function () use ($id, $status) {
            $update = ['status' => $status, 'updated_by' => auth()->id()];
            if ($status === \App\Enums\TaskStatus::Done->value) {
                $update['completed_at'] = now();
            }
            return $this->repository->update($id, $update);
        });
    }

    public function addComment(int $id, array $data): Task
    {
        return DB::transaction(function () use ($id, $data) {
            $task = $this->repository->findById($id);
            $data['user_id'] = auth()->id();
            $data['created_by'] = auth()->id();
            $task->comments()->create($data);
            return $this->repository->findWithDetails($id);
        });
    }
}
