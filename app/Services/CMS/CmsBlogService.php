<?php

namespace App\Services\CMS;

use App\Models\CmsBlog;
use App\Repositories\CmsBlogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CmsBlogService
{
    public function __construct(
        protected CmsBlogRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): CmsBlog
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): CmsBlog
    {
        return DB::transaction(function () use ($data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            $data['author_id'] = $data['author_id'] ?? auth()->id();
            $data['created_by'] = auth()->id();
            $blog = $this->repository->create($data);

            if (!empty($tags)) {
                $blog->tags()->sync($tags);
            }

            return $blog->fresh(['category', 'author', 'tags']);
        });
    }

    public function update(int $id, array $data): CmsBlog
    {
        return DB::transaction(function () use ($id, $data) {
            $tags = $data['tags'] ?? null;
            unset($data['tags']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($tags !== null) {
                $blog = $this->repository->findById($id);
                $blog->tags()->sync($tags);
            }

            return $this->repository->findById($id)->fresh(['category', 'author', 'tags']);
        });
    }

    public function publish(int $id): CmsBlog
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => \App\Enums\PageStatus::Published,
                'published_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
