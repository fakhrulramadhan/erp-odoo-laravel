<?php

namespace App\Services\Recruitment;

use App\Models\Interview;
use App\Repositories\InterviewRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InterviewService
{
    public function __construct(
        protected InterviewRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Interview
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Interview
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Interview
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

    public function submitFeedback(int $id, array $data): Interview
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }
}
