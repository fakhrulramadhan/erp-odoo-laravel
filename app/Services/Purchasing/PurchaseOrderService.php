<?php

namespace App\Services\Purchasing;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Repositories\PurchaseOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseOrderService
{
    public function __construct(
        protected PurchaseOrderRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): PurchaseOrder
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['order_number'] = $this->repository->getNextOrderNumber();
            $data['status'] = PurchaseOrderStatus::Draft;
            $data['created_by'] = auth()->id();

            $po = $this->repository->create($data);

            foreach ($lines as $index => $line) {
                $line['line_number'] = $index + 1;
                $po->lines()->create($line);
            }

            $po->recalculate();

            return $this->repository->findWithLines($po->id);
        });
    }

    public function update(int $id, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $po = $this->repository->findById($id);

            if (!in_array($po->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::WaitingApproval])) {
                throw new \DomainException('Cannot edit a purchase order that is not in draft or waiting approval status.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($lines !== null) {
                $po->lines()->delete();
                foreach ($lines as $index => $line) {
                    $line['line_number'] = $index + 1;
                    $po->lines()->create($line);
                }
            }

            $po->fresh()->recalculate();

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findById($id);

            if (!in_array($po->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Cancelled])) {
                throw new \DomainException('Cannot delete a purchase order that is not in draft or cancelled status.');
            }

            return $this->repository->delete($id);
        });
    }

    public function submitForApproval(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findById($id);
            $po->transitionTo(PurchaseOrderStatus::WaitingApproval);
            return $this->repository->findWithLines($id);
        });
    }

    public function approve(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findById($id);
            $po->transitionTo(PurchaseOrderStatus::Approved);
            $po->update([
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            return $this->repository->findWithLines($id);
        });
    }

    public function cancel(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findById($id);
            $po->transitionTo(PurchaseOrderStatus::Cancelled);
            $po->update(['cancelled_by' => auth()->id(), 'cancelled_at' => now()]);
            return $this->repository->findWithLines($id);
        });
    }

    public function markAsOrdered(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findById($id);
            $po->transitionTo(PurchaseOrderStatus::Ordered);
            return $this->repository->findWithLines($id);
        });
    }

    public function sendToVendor(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findById($id);
            $po->transitionTo(PurchaseOrderStatus::Ordered);
            $po->update(['sent_at' => now()]);
            return $this->repository->findWithLines($id);
        });
    }
}
