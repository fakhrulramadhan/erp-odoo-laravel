<?php

namespace App\Services\Workflow;

use App\Models\Workflow;
use App\Repositories\WorkflowRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    public function __construct(
        protected WorkflowRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Workflow
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Workflow
    {
        return DB::transaction(function () use ($data) {
            $steps = $data['steps'] ?? [];
            unset($data['steps']);

            $data['status'] = $data['status'] ?? \App\Enums\WorkflowStatus::Draft;
            $data['created_by'] = auth()->id();
            $workflow = $this->repository->create($data);

            foreach ($steps as $index => $step) {
                $step['sequence'] = $index + 1;
                $workflow->steps()->create($step);
            }

            return $this->repository->findWithDetails($workflow->id);
        });
    }

    public function update(int $id, array $data): Workflow
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function activate(int $id): Workflow
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => \App\Enums\WorkflowStatus::Active,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function deactivate(int $id): Workflow
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => \App\Enums\WorkflowStatus::Inactive,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
