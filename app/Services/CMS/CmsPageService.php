<?php

namespace App\Services\CMS;

use App\Models\CmsPage;
use App\Repositories\CmsPageRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CmsPageService
{
    public function __construct(
        protected CmsPageRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): CmsPage
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): CmsPage
    {
        return DB::transaction(function () use ($data) {
            $data['author_id'] = $data['author_id'] ?? auth()->id();
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): CmsPage
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function publish(int $id): CmsPage
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => \App\Enums\PageStatus::Published,
                'published_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function unpublish(int $id): CmsPage
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => \App\Enums\PageStatus::Draft,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
