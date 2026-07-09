<?php

namespace App\Services\Purchasing;

use App\Models\VendorPricelist;
use App\Repositories\VendorPricelistRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class VendorPricelistService
{
    public function __construct(
        protected VendorPricelistRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): VendorPricelist
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): VendorPricelist
    {
        $data['created_by'] = auth()->id();
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): VendorPricelist
    {
        $data['updated_by'] = auth()->id();
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function getBestPrice(int $productId, float $quantity = 1): ?VendorPricelist
    {
        return $this->repository->getBestPrice($productId, $quantity);
    }

    public function getPricesForProduct(int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getPricesForProduct($productId);
    }
}
