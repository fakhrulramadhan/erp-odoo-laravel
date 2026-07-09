<?php

namespace App\Services\Manufacturing;

use App\Models\AssetCategory;
use App\Repositories\AssetCategoryRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class AssetCategoryService
{
    public function __construct(
        protected AssetCategoryRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): AssetCategory
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(array $data): AssetCategory
    {
        return DB::transaction(function () use ($data) {
            $data['code'] = $data['code'] ?? $this->repository->getNextCode();
            $data['created_by'] = auth()->id();

            $category = $this->repository->create($data);
            return $this->repository->findWithRelations($category->id);
        });
    }

    public function update(int $id, array $data): AssetCategory
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);
            return $this->repository->findWithRelations($id);
        });
    }

    public function delete(int $id): bool
    {
        $category = $this->repository->findById($id);
        if ($category->assets()->count() > 0) {
            throw new \DomainException('Cannot delete category with existing assets.');
        }
        return $this->repository->delete($id);
    }
}
