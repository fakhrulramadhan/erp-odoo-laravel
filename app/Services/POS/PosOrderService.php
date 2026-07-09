<?php

namespace App\Services\POS;

use App\Models\PosOrder;
use App\Repositories\PosOrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PosOrderService
{
    public function __construct(
        protected PosOrderRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): PosOrder
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): PosOrder
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            $payments = $data['payments'] ?? [];
            unset($data['lines'], $data['payments']);

            $data['order_number'] = $this->repository->getNextOrderNumber();
            $data['status'] = 'completed';
            $data['created_by'] = auth()->id();

            $order = $this->repository->create($data);

            foreach ($lines as $line) {
                $order->lines()->create($line);
            }

            foreach ($payments as $payment) {
                $order->payments()->create($payment);
            }

            return $this->repository->findWithDetails($order->id);
        });
    }

    public function refund(int $id): PosOrder
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => 'refunded',
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
