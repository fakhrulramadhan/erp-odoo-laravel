<?php

namespace App\Services\Recruitment;

use App\Models\JobVacancy;
use App\Repositories\JobVacancyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JobVacancyService
{
    public function __construct(
        protected JobVacancyRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): JobVacancy
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): JobVacancy
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): JobVacancy
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

    public function publish(int $id): JobVacancy
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'open',
                'published_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function close(int $id): JobVacancy
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'closed',
                'closed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }
}
