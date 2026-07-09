<?php

namespace App\Services\Manufacturing;

use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceOrder;
use App\Repositories\MaintenanceOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class MaintenanceOrderService
{
    public function __construct(
        protected MaintenanceOrderRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): MaintenanceOrder
    {
        return $this->repository->findWithRelations($id);
    }

    public function create(array $data): MaintenanceOrder
    {
        return DB::transaction(function () use ($data) {
            $data['order_number'] = $this->repository->getNextOrderNumber();
            $data['status'] = MaintenanceStatus::Draft;
            $data['created_by'] = auth()->id();

            $order = $this->repository->create($data);
            return $this->repository->findWithRelations($order->id);
        });
    }

    public function update(int $id, array $data): MaintenanceOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->repository->findById($id);

            if (!in_array($order->status, [MaintenanceStatus::Draft, MaintenanceStatus::Requested])) {
                throw new \DomainException('Cannot edit maintenance order unless draft or requested.');
            }

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            return $this->repository->findWithRelations($id);
        });
    }

    public function schedule(int $id, array $data): MaintenanceOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->repository->findById($id);
            $order->transitionTo(MaintenanceStatus::Scheduled);
            $order->update([
                'scheduled_date' => $data['scheduled_date'] ?? now(),
                'assigned_to' => $data['assigned_to'] ?? $order->assigned_to,
                'updated_by' => auth()->id(),
            ]);
            return $this->repository->findWithRelations($id);
        });
    }

    public function startWork(int $id): MaintenanceOrder
    {
        return DB::transaction(function () use ($id) {
            $order = $this->repository->findById($id);
            $order->transitionTo(MaintenanceStatus::InProgress);
            $order->update([
                'actual_start' => now(),
                'updated_by' => auth()->id(),
            ]);
            return $this->repository->findWithRelations($id);
        });
    }

    public function complete(int $id, array $data = []): MaintenanceOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->repository->findById($id);
            $order->transitionTo(MaintenanceStatus::Completed);
            $order->update([
                'actual_finish' => now(),
                'actual_duration' => $data['actual_duration'] ?? $order->actual_duration,
                'notes' => $data['notes'] ?? $order->notes,
                'updated_by' => auth()->id(),
            ]);

            // Update equipment maintenance dates
            if ($order->equipment) {
                $order->equipment->update([
                    'last_maintenance_date' => now(),
                    'next_maintenance_date' => $order->scheduled_date ? now()->addDays($order->equipment->maintenance_interval_days ?? 90) : null,
                ]);
            }

            return $this->repository->findWithRelations($id);
        });
    }

    public function cancel(int $id): MaintenanceOrder
    {
        return DB::transaction(function () use ($id) {
            $order = $this->repository->findById($id);
            $order->transitionTo(MaintenanceStatus::Cancelled);
            return $this->repository->findWithRelations($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $order = $this->repository->findById($id);
            if ($order->status !== MaintenanceStatus::Draft) {
                throw new \DomainException('Cannot delete a maintenance order that is not in draft status.');
            }
            return $this->repository->delete($id);
        });
    }
}
