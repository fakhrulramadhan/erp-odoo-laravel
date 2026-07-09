<?php

namespace App\Services\Ecommerce;

use App\Models\EcommerceProduct;
use App\Repositories\EcommerceProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EcommerceProductService
{
    public function __construct(
        protected EcommerceProductRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): EcommerceProduct
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): EcommerceProduct
    {
        return DB::transaction(function () use ($data) {
            $images = $data['images'] ?? [];
            $variants = $data['variants'] ?? [];
            unset($data['images'], $data['variants']);

            $data['created_by'] = auth()->id();
            $product = $this->repository->create($data);

            foreach ($images as $index => $image) {
                $image['sequence'] = $index + 1;
                $product->images()->create($image);
            }

            foreach ($variants as $variant) {
                $product->variants()->create($variant);
            }

            return $this->repository->findWithDetails($product->id);
        });
    }

    public function update(int $id, array $data): EcommerceProduct
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
}
