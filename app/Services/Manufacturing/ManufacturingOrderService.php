<?php

namespace App\Services\Manufacturing;

use App\Enums\ManufacturingOrderStatus;
use App\Enums\StockMoveStatus;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderLine;
use App\Models\StockQuant;
use App\Repositories\ManufacturingOrderRepository;
use App\Services\Finance\FinanceService;
use App\Services\Inventory\InventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ManufacturingOrderService
{
    public function __construct(
        protected ManufacturingOrderRepository $repository,
        protected FinanceService $financeService,
        protected InventoryService $inventoryService,
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): ManufacturingOrder
    {
        return $this->repository->findWithLines($id);
    }

    public function create(array $data): ManufacturingOrder
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['order_number'] = $this->repository->getNextOrderNumber();
            $data['status'] = ManufacturingOrderStatus::Draft;
            $data['created_by'] = auth()->id();

            // Auto-populate lines from BOM if not provided
            if (empty($lines) && !empty($data['bom_id'])) {
                $bom = \App\Models\BillOfMaterial::with('lines.product')->findOrFail($data['bom_id']);
                foreach ($bom->lines as $index => $bomLine) {
                    $lines[] = [
                        'line_number' => $index + 1,
                        'product_id' => $bomLine->product_id,
                        'uom_id' => $bomLine->uom_id,
                        'quantity' => $bomLine->quantity * ($data['quantity'] ?? 1),
                        'line_type' => 'component',
                        'source_location_id' => $data['source_location_id'] ?? null,
                    ];
                }
            }

            $mo = $this->repository->create($data);

            foreach ($lines as $line) {
                $mo->lines()->create($line);
            }

            return $this->repository->findWithLines($mo->id);
        });
    }

    public function update(int $id, array $data): ManufacturingOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $mo = $this->repository->findById($id);

            if (!in_array($mo->status, [ManufacturingOrderStatus::Draft, ManufacturingOrderStatus::Confirmed])) {
                throw new \DomainException('Cannot edit a manufacturing order that is not in draft or confirmed status.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->repository->update($id, $data);

            if ($lines !== null) {
                $mo->lines()->delete();
                foreach ($lines as $index => $line) {
                    $line['line_number'] = $index + 1;
                    $mo->lines()->create($line);
                }
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findById($id);
            if (!in_array($mo->status, [ManufacturingOrderStatus::Draft, ManufacturingOrderStatus::Cancelled])) {
                throw new \DomainException('Cannot delete a manufacturing order that is not in draft or cancelled status.');
            }
            return $this->repository->delete($id);
        });
    }

    public function confirm(int $id): ManufacturingOrder
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findById($id);
            $mo->transitionTo(ManufacturingOrderStatus::Confirmed);
            return $this->repository->findWithLines($id);
        });
    }

    public function reserveMaterials(int $id): ManufacturingOrder
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findWithLines($id);
            $mo->transitionTo(ManufacturingOrderStatus::MaterialReserved);

            // Reserve materials in inventory
            foreach ($mo->lines as $line) {
                $locationId = $line->source_location_id ?? $mo->source_location_id;
                if (!$locationId) continue;

                $quant = StockQuant::firstOrCreate(
                    ['product_id' => $line->product_id, 'location_id' => $locationId],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => 0]
                );

                $quant->update([
                    'reserved_quantity' => (float) $quant->reserved_quantity + (float) $line->quantity,
                    'available_quantity' => (float) $quant->quantity - ((float) $quant->reserved_quantity + (float) $line->quantity),
                ]);

                $line->update(['reserved_qty' => $line->quantity]);
            }

            return $this->repository->findWithLines($id);
        });
    }

    public function startProduction(int $id): ManufacturingOrder
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findById($id);
            $mo->transitionTo(ManufacturingOrderStatus::InProduction);
            $mo->update(['actual_start' => now()]);

            return $this->repository->findWithLines($id);
        });
    }

    public function finishProduction(int $id, array $data = []): ManufacturingOrder
    {
        return DB::transaction(function () use ($id, $data) {
            $mo = $this->repository->findWithLines($id);

            // Update produced qty and scrap qty
            if (isset($data['produced_qty'])) {
                $mo->update(['produced_qty' => $data['produced_qty']]);
            }
            if (isset($data['scrap_qty'])) {
                $mo->update(['scrap_qty' => $data['scrap_qty']]);
            }

            // Consume raw materials (reduce inventory)
            foreach ($mo->lines as $line) {
                $locationId = $line->source_location_id ?? $mo->source_location_id;
                if (!$locationId) continue;

                $quant = StockQuant::firstOrCreate(
                    ['product_id' => $line->product_id, 'location_id' => $locationId],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => 0]
                );

                $consumeQty = (float) $line->quantity;
                $quant->removeQuantity($consumeQty);
                $quant->update([
                    'reserved_quantity' => max(0, (float) $quant->reserved_quantity - $consumeQty),
                ]);

                $line->update([
                    'consumed_qty' => $consumeQty,
                    'unit_cost' => (float) $quant->unit_cost,
                    'total_cost' => round($consumeQty * (float) $quant->unit_cost, 2),
                ]);
            }

            // Add finished goods to inventory
            $destLocationId = $mo->destination_location_id;
            if ($destLocationId && (float) $mo->produced_qty > 0) {
                $fgQuant = StockQuant::firstOrCreate(
                    ['product_id' => $mo->product_id, 'location_id' => $destLocationId],
                    ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => 0]
                );
                $fgQuant->addQuantity((float) $mo->produced_qty, (float) $mo->unit_cost);
            }

            // Record production costs
            $this->recordProductionCosts($mo);

            // Create accounting journal: WIP -> Finished Goods
            $this->createProductionJournal($mo);

            $mo->update(['actual_finish' => now()]);

            // Check if QC is needed
            $mo->transitionTo(ManufacturingOrderStatus::QualityCheck);

            return $this->repository->findWithLines($id);
        });
    }

    public function markFinished(int $id): ManufacturingOrder
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findById($id);
            $mo->transitionTo(ManufacturingOrderStatus::Finished);
            return $this->repository->findWithLines($id);
        });
    }

    public function close(int $id): ManufacturingOrder
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findById($id);
            $mo->transitionTo(ManufacturingOrderStatus::Closed);
            return $this->repository->findWithLines($id);
        });
    }

    public function cancel(int $id): ManufacturingOrder
    {
        return DB::transaction(function () use ($id) {
            $mo = $this->repository->findWithLines($id);

            // Unreserve materials
            if ($mo->status === ManufacturingOrderStatus::MaterialReserved) {
                foreach ($mo->lines as $line) {
                    $locationId = $line->source_location_id ?? $mo->source_location_id;
                    if (!$locationId) continue;

                    $quant = StockQuant::where('product_id', $line->product_id)
                        ->where('location_id', $locationId)
                        ->first();

                    if ($quant) {
                        $quant->update([
                            'reserved_quantity' => max(0, (float) $quant->reserved_quantity - (float) $line->reserved_qty),
                            'available_quantity' => (float) $quant->quantity - max(0, (float) $quant->reserved_quantity - (float) $line->reserved_qty),
                        ]);
                    }
                }
            }

            $mo->transitionTo(ManufacturingOrderStatus::Cancelled);
            return $this->repository->findWithLines($id);
        });
    }

    protected function recordProductionCosts(ManufacturingOrder $mo): void
    {
        $mo->productionCosts()->delete();

        // Material costs
        foreach ($mo->lines as $line) {
            $mo->productionCosts()->create([
                'cost_type' => 'material',
                'description' => 'Material: ' . ($line->product?->name ?? 'Unknown'),
                'quantity' => $line->consumed_qty,
                'unit_cost' => $line->unit_cost,
                'total_cost' => $line->total_cost,
                'product_id' => $line->product_id,
            ]);
        }

        // Labor & machine costs from work orders
        foreach ($mo->workOrders as $wo) {
            if ($wo->cost > 0) {
                $mo->productionCosts()->create([
                    'cost_type' => 'labor',
                    'description' => 'Work Order: ' . $wo->name,
                    'quantity' => $wo->actual_duration_minutes / 60,
                    'unit_cost' => $wo->workCenter?->cost_per_hour ?? 0,
                    'total_cost' => $wo->cost,
                    'work_center_id' => $wo->work_center_id,
                ]);
            }
        }

        $mo->recalculate();
    }

    protected function createProductionJournal(ManufacturingOrder $mo): void
    {
        if ($mo->total_cost <= 0) return;

        try {
            // Debit: Finished Goods (Inventory)
            // Credit: Work In Progress (WIP)
            $journal = \App\Models\Journal::where('code', 'MJ')->first()
                ?? \App\Models\Journal::first();

            if (!$journal) return;

            $finishedGoodsAccount = \App\Models\Account::where('code', '1200')->first(); // Finished Goods
            $wipAccount = \App\Models\Account::where('code', '1300')->first(); // WIP

            if (!$finishedGoodsAccount || !$wipAccount) return;

            $this->financeService->createJournalEntry([
                'journal_id' => $journal->id,
                'entry_date' => now()->toDateString(),
                'description' => "Production completion: {$mo->order_number}",
                'reference' => $mo->order_number,
                'lines' => [
                    [
                        'account_id' => $finishedGoodsAccount->id,
                        'description' => 'Finished Goods - ' . ($mo->product?->name ?? ''),
                        'debit_amount' => $mo->total_cost,
                        'credit_amount' => 0,
                    ],
                    [
                        'account_id' => $wipAccount->id,
                        'description' => 'WIP - ' . $mo->order_number,
                        'debit_amount' => 0,
                        'credit_amount' => $mo->total_cost,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create production journal: ' . $e->getMessage());
        }
    }
}
