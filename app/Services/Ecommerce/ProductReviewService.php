<?php

namespace App\Services\Ecommerce;

use App\Models\ProductReview;
use App\Repositories\ProductReviewRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductReviewService
{
    public function __construct(
        protected ProductReviewRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): ProductReview
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): ProductReview
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function approve(int $id): ProductReview
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'is_approved' => true,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
