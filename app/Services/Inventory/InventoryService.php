<?php

namespace App\Services\Inventory;

use App\Models\InventoryAdjustment;
use App\Models\StockQuant;
use App\Repositories\InventoryAdjustmentRepository;
use App\Repositories\StockQuantRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryService
{
    public function __construct(
        protected InventoryAdjustmentRepository $adjustmentRepository,
        protected StockQuantRepository $quantRepository,
        protected InventoryValuationService $valuationService,
    ) {}

    public function listAdjustments(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->adjustmentRepository->getWithRelations($filters, $perPage);
    }

    public function findAdjustment(int $id): InventoryAdjustment
    {
        return $this->adjustmentRepository->findWithLines($id);
    }

    public function createAdjustment(array $data): InventoryAdjustment
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $data['adjustment_number'] = $this->adjustmentRepository->getNextAdjustmentNumber();
            $data['status'] = 'draft';
            $data['created_by'] = auth()->id();
            $data['company_id'] ??= auth()->user()?->company_id
                ?? \App\Models\Company::query()->orderBy('id')->value('id');
            $data['adjustment_date'] ??= now()->toDateString();

            $adjustment = $this->adjustmentRepository->create($data);

            foreach ($lines as $line) {
                $adjustment->lines()->create($line);
            }

            return $this->adjustmentRepository->findWithLines($adjustment->id);
        });
    }

    public function updateAdjustment(int $id, array $data): InventoryAdjustment
    {
        return DB::transaction(function () use ($id, $data) {
            $adjustment = $this->adjustmentRepository->findById($id);

            if ($adjustment->status !== 'draft') {
                throw new \DomainException('Can only edit draft adjustments.');
            }

            $lines = $data['lines'] ?? null;
            unset($data['lines']);

            $data['updated_by'] = auth()->id();
            $this->adjustmentRepository->update($id, $data);

            if ($lines !== null) {
                $adjustment->lines()->delete();
                foreach ($lines as $line) {
                    $adjustment->lines()->create($line);
                }
            }

            return $this->adjustmentRepository->findWithLines($id);
        });
    }

    public function validateAdjustment(int $id): InventoryAdjustment
    {
        return DB::transaction(function () use ($id) {
            $adjustment = $this->adjustmentRepository->findWithLines($id);

            if ($adjustment->status !== 'draft') {
                throw new \DomainException('Can only validate draft adjustments.');
            }

            foreach ($adjustment->lines as $line) {
                $diff = (float) ($line->actual_qty - $line->theoretical_qty);

                if ($diff !== 0.0) {
                    $quant = StockQuant::firstOrCreate(
                        ['product_id' => $line->product_id, 'location_id' => $line->location_id],
                        ['quantity' => 0, 'reserved_quantity' => 0, 'available_quantity' => 0, 'unit_cost' => 0]
                    );

                    if ($diff > 0) {
                        $quant->addQuantity(abs($diff));
                    } else {
                        $quant->removeQuantity(abs($diff));
                    }

                    $line->update(['difference' => $diff]);

                    // Value the stock difference at the location's unit cost.
                    $this->valuationService->recordAdjustment(
                        $adjustment->adjustment_number,
                        $diff * (float) $quant->unit_cost,
                        "Stock adjustment {$adjustment->adjustment_number} - " . ($line->product?->name ?? 'product')
                    );
                }
            }

            $adjustment->update([
                'status' => 'validated',
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

            return $this->adjustmentRepository->findWithLines($id);
        });
    }

    public function deleteAdjustment(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $adjustment = $this->adjustmentRepository->findById($id);
            if ($adjustment->status !== 'draft') {
                throw new \DomainException('Can only delete draft adjustments.');
            }
            return $this->adjustmentRepository->delete($id);
        });
    }

    public function listQuants(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->quantRepository->getWithRelations($filters, $perPage);
    }

    public function getProductStock(int $productId, ?int $warehouseId = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->quantRepository->getForProduct($productId, $warehouseId);
    }

    public function getTotalOnHand(int $productId): float
    {
        return $this->quantRepository->getTotalOnHand($productId);
    }

    public function getLowStock(?int $warehouseId = null): \Illuminate\Database\Eloquent\Collection
    {
        return $this->quantRepository->getLowStockProducts($warehouseId);
    }

    /**
     * Stock card: every movement for a product with a running balance.
     * Incoming moves add, outgoing moves subtract, internal transfers net to
     * zero but are still listed so the trail is complete.
     */
    public function getStockLedger(int $productId, array $filters = []): array
    {
        $query = \App\Models\StockMove::query()
            ->with(['stockPicking:id,picking_number,picking_type,origin', 'sourceLocation:id,name', 'destinationLocation:id,name'])
            ->where('product_id', $productId)
            ->where('status', \App\Enums\StockMoveStatus::Done->value)
            ->when(!empty($filters['location_id']), function ($q) use ($filters) {
                $q->where(function ($sub) use ($filters) {
                    $sub->where('source_location_id', $filters['location_id'])
                        ->orWhere('destination_location_id', $filters['location_id']);
                });
            })
            ->when(!empty($filters['date_from']), fn ($q) => $q->whereDate('effective_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($q) => $q->whereDate('effective_date', '<=', $filters['date_to']))
            ->orderBy('effective_date')
            ->orderBy('id');

        $running = 0.0;
        $rows = [];

        foreach ($query->get() as $move) {
            $qty = (float) $move->done_qty;
            $type = $move->stockPicking?->picking_type?->value;

            // Incoming increases stock; outgoing and scrap decrease it.
            if ($type === \App\Enums\PickingType::Incoming->value) {
                $delta = $qty;
            } elseif ($type === \App\Enums\PickingType::Outgoing->value || $type === \App\Enums\PickingType::Dropship->value) {
                $delta = -$qty;
            } else {
                // Internal transfers only move stock between locations.
                $delta = 0.0;
            }

            $running = round($running + $delta, 4);

            $rows[] = [
                'id' => $move->id,
                'date' => $move->effective_date?->toDateString(),
                'picking_number' => $move->stockPicking?->picking_number,
                'picking_type' => $type,
                'origin' => $move->origin,
                'from' => $move->sourceLocation?->name,
                'to' => $move->destinationLocation?->name,
                'quantity' => $qty,
                'change' => $delta,
                'unit_cost' => (float) $move->unit_cost,
                'value' => round($delta * (float) $move->unit_cost, 2),
                'sort_key' => str_pad((string) $move->id, 10, '0', STR_PAD_LEFT),
                'running_balance' => $running,
            ];
        }

        $product = \App\Models\Product::find($productId, ['id', 'code', 'name']);

        // Stock adjustments and scrap orders change quants without creating a
        // stock_move, so fold them in or the card cannot explain the balance.
        $adjustments = \App\Models\InventoryAdjustmentLine::query()
            ->join('inventory_adjustments', 'inventory_adjustments.id', '=', 'inventory_adjustment_lines.inventory_adjustment_id')
            ->where('inventory_adjustment_lines.product_id', $productId)
            ->where('inventory_adjustments.status', 'validated')
            ->when(!empty($filters['date_from']), fn ($q) => $q->whereDate('inventory_adjustments.adjustment_date', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($q) => $q->whereDate('inventory_adjustments.adjustment_date', '<=', $filters['date_to']))
            ->get([
                'inventory_adjustment_lines.id',
                'inventory_adjustment_lines.difference',
                'inventory_adjustments.adjustment_number',
                'inventory_adjustments.adjustment_date',
            ]);

        foreach ($adjustments as $adj) {
            $delta = (float) $adj->difference;
            if (abs($delta) < 0.0001) {
                continue;
            }

            $rows[] = [
                'id' => 'adjustment-' . $adj->id,
                'date' => $adj->adjustment_date,
                'picking_number' => $adj->adjustment_number,
                'picking_type' => 'adjustment',
                'origin' => null,
                'from' => null,
                'to' => null,
                'quantity' => abs($delta),
                'change' => $delta,
                'unit_cost' => 0.0,
                'value' => 0.0,
                'running_balance' => 0.0,
            ];
        }

        $scraps = \App\Models\ScrapOrder::query()
            ->where('product_id', $productId)
            ->where('status', 'done')
            ->when(!empty($filters['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $filters['date_to']))
            ->get(['id', 'scrap_number', 'quantity', 'unit_cost', 'created_at']);

        foreach ($scraps as $scrap) {
            $qty = (float) $scrap->quantity;

            $rows[] = [
                'id' => 'scrap-' . $scrap->id,
                'date' => $scrap->created_at?->toDateString(),
                'picking_number' => $scrap->scrap_number,
                'picking_type' => 'scrap',
                'origin' => null,
                'from' => null,
                'to' => null,
                'quantity' => $qty,
                'change' => -$qty,
                'unit_cost' => (float) $scrap->unit_cost,
                'value' => round(-$qty * (float) $scrap->unit_cost, 2),
                'running_balance' => 0.0,
            ];
        }

        // Moves were collected in date order already; sort the merged rows by
        // date and recompute the running balance across everything.
        usort($rows, fn ($a, $b) => [$a['date'] ?? '', (string) $a['id']] <=> [$b['date'] ?? '', (string) $b['id']]);

        // Stock that existed before any recorded movement has no move row, so
        // derive the opening balance from the quants. This guarantees the
        // closing balance on the card always equals the real on-hand quantity.
        $onHand = (float) \App\Models\StockQuant::where('product_id', $productId)->sum('quantity');
        $netMovement = array_sum(array_map(fn ($r) => $r['change'], $rows));
        $opening = round($onHand - $netMovement, 4);

        $running = $opening;
        foreach ($rows as $index => $row) {
            $running = round($running + $row['change'], 4);
            $rows[$index]['running_balance'] = $running;
        }

        return [
            'product' => $product ? ['id' => $product->id, 'code' => $product->code, 'name' => $product->name] : null,
            'opening_balance' => $opening,
            'closing_balance' => $running,
            'on_hand' => $onHand,
            'total_in' => round(array_sum(array_map(fn ($r) => $r['change'] > 0 ? $r['change'] : 0, $rows)), 4),
            'total_out' => round(abs(array_sum(array_map(fn ($r) => $r['change'] < 0 ? $r['change'] : 0, $rows))), 4),
            'rows' => $rows,
        ];    }
}
