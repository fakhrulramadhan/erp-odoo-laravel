<?php

namespace App\Services\Ecommerce;

use App\Enums\EcommerceOrderStatus;
use App\Models\EcommerceOrder;
use App\Repositories\EcommerceOrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EcommerceOrderService
{
    public function __construct(
        protected EcommerceOrderRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): EcommerceOrder
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): EcommerceOrder
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['order_number'] = $this->repository->getNextOrderNumber();
            $data['status'] = EcommerceOrderStatus::Pending;
            $data['created_by'] = auth()->id();
            $order = $this->repository->create($data);

            foreach ($lines as $line) {
                $order->lines()->create($line);
            }

            return $this->repository->findWithDetails($order->id);
        });
    }

    public function update(int $id, array $data): EcommerceOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function updateStatus(int $id, string $status): EcommerceOrder
    {
        return DB::transaction(function () use ($id, $status) {
            return $this->repository->update($id, [
                'status' => $status,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
