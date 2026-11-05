<?php

namespace App\Services\Purchasing;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PickingStatus;
use App\Enums\PickingType;
use App\Enums\StockMoveStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\StockLocation;
use App\Models\StockMove;
use App\Models\StockPicking;
use App\Repositories\PurchaseOrderRepository;
use App\Services\Inventory\StockPickingService;
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

    public function createPicking(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findWithLines($id);

            if (!in_array($po->status, [PurchaseOrderStatus::Ordered, PurchaseOrderStatus::PartialReceived])) {
                throw new \DomainException('Cannot create picking for a purchase order that is not ordered or partially received.');
            }

            $existing = $po->pickings()->where('status', '!=', PickingStatus::Cancelled->value)->first();
            if ($existing) {
                throw new \DomainException('A stock picking already exists for this purchase order.');
            }

            $warehouseId = $po->warehouse_id;
            $destinationLocation = StockLocation::where('warehouse_id', $warehouseId)
                ->where('type', 'internal')
                ->first();
            if (!$destinationLocation) {
                throw new \DomainException('No internal stock location found for the purchase order warehouse.');
            }

            $picking = StockPicking::create([
                'picking_number' => app(\App\Repositories\StockPickingRepository::class)->getNextPickingNumber(PickingType::Incoming->value),
                'company_id' => $po->company_id,
                'picking_type' => PickingType::Incoming,
                'status' => PickingStatus::Draft,
                'origin' => 'purchase:' . $po->id,
                'source_location_id' => null,
                'destination_location_id' => $destinationLocation->id,
                'vendor_id' => $po->vendor_id,
                'scheduled_date' => $po->expected_date ?? now(),
                'purchase_order_id' => $po->id,
                'notes' => 'Goods receipt for ' . $po->order_number,
                'created_by' => auth()->id(),
            ]);

            foreach ($po->lines as $line) {
                $remaining = (float) $line->quantity - (float) $line->received_qty;
                if ($remaining <= 0) {
                    continue;
                }
                $picking->moves()->create([
                    'product_id' => $line->product_id,
                    'uom_id' => $line->uom_id,
                    'source_location_id' => null,
                    'destination_location_id' => $destinationLocation->id,
                    'quantity' => $remaining,
                    'done_qty' => 0,
                    'unit_cost' => $line->price,
                    'total_cost' => $remaining * $line->price,
                    'origin' => 'po-line:' . $line->id,
                    'status' => StockMoveStatus::Draft,
                    'created_by' => auth()->id(),
                ]);
            }

            $po->transitionTo(PurchaseOrderStatus::Ordered);

            return $this->repository->findWithLines($id);
        });
    }

    public function receive(int $id): PurchaseOrder
    {
        return DB::transaction(function () use ($id) {
            $po = $this->repository->findWithLines($id);

            if (!in_array($po->status, [PurchaseOrderStatus::Ordered, PurchaseOrderStatus::PartialReceived])) {
                throw new \DomainException('Cannot receive a purchase order that is not ordered or partially received.');
            }

            $picking = $po->pickings()->where('status', PickingStatus::Done->value)->first();
            if (!$picking) {
                throw new \DomainException('No validated stock picking found for this purchase order.');
            }

            foreach ($po->lines as $line) {
                $received = 0;
                foreach ($picking->moves as $move) {
                    if ($move->product_id === $line->product_id) {
                        $received += (float) $move->done_qty;
                    }
                }
                $line->update(['received_qty' => $received]);
            }

            $po->recalculate();

            if ($po->isFullyReceived()) {
                $po->transitionTo(PurchaseOrderStatus::Received);
            } else {
                $po->transitionTo(PurchaseOrderStatus::PartialReceived);
            }

            return $this->repository->findWithLines($id);
        });
    }
}
