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
}
