<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryAdjustmentResource;
use App\Http\Resources\StockQuantResource;
use App\Services\Inventory\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InventoryService $service
    ) {}

    // ─── Stock Quants ──────────────────────────────

    public function quants(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'product_id', 'location_id', 'warehouse_id', 'low_stock']);
        $perPage = $request->input('per_page', 15);

        $paginator = $this->service->listQuants($filters, $perPage);
        $paginator->getCollection()->transform(fn($q) => (new StockQuantResource($q))->resolve($request));

        return $this->paginated($paginator);
    }

    public function productStock(int $productId, Request $request): JsonResponse
    {
        $warehouseId = $request->input('warehouse_id');
        $quants = $this->service->getProductStock($productId, $warehouseId);

        return $this->success(StockQuantResource::collection($quants));
    }

    public function totalOnHand(int $productId): JsonResponse
    {
        $total = $this->service->getTotalOnHand($productId);

        return $this->success(['product_id' => $productId, 'total_on_hand' => $total]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $warehouseId = $request->input('warehouse_id');
        $quants = $this->service->getLowStock($warehouseId);

        return $this->success(StockQuantResource::collection($quants));
    }

    // ─── Inventory Adjustments ─────────────────────

    public function adjustments(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'warehouse_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);

        $paginator = $this->service->listAdjustments($filters, $perPage);
        $paginator->getCollection()->transform(fn($a) => (new InventoryAdjustmentResource($a))->resolve($request));

        return $this->paginated($paginator);
    }

    public function createAdjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_date' => 'required|date',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.location_id' => 'required|exists:stock_locations,id',
            'lines.*.lot_id' => 'nullable|exists:lots,id',
            'lines.*.theoretical_qty' => 'required|numeric',
            'lines.*.actual_qty' => 'required|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        // Calculate difference for each line
        foreach ($validated['lines'] as &$line) {
            $line['difference'] = $line['actual_qty'] - $line['theoretical_qty'];
        }

        $adjustment = $this->service->createAdjustment($validated);

        return $this->created(new InventoryAdjustmentResource($adjustment), 'Inventory adjustment created successfully.');
    }

    public function showAdjustment(int $id): JsonResponse
    {
        $adjustment = $this->service->findAdjustment($id);

        return $this->success(new InventoryAdjustmentResource($adjustment));
    }

    public function updateAdjustment(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'adjustment_date' => 'sometimes|required|date',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.location_id' => 'required|exists:stock_locations,id',
            'lines.*.lot_id' => 'nullable|exists:lots,id',
            'lines.*.theoretical_qty' => 'required|numeric',
            'lines.*.actual_qty' => 'required|numeric|min:0',
            'lines.*.notes' => 'nullable|string',
        ]);

        if (isset($validated['lines'])) {
            foreach ($validated['lines'] as &$line) {
                $line['difference'] = $line['actual_qty'] - $line['theoretical_qty'];
            }
        }

        $adjustment = $this->service->updateAdjustment($id, $validated);

        return $this->success(new InventoryAdjustmentResource($adjustment), 'Inventory adjustment updated successfully.');
    }

    public function validateAdjustment(int $id): JsonResponse
    {
        $adjustment = $this->service->validateAdjustment($id);

        return $this->success(new InventoryAdjustmentResource($adjustment), 'Inventory adjustment validated. Stock has been updated.');
    }

    public function deleteAdjustment(int $id): JsonResponse
    {
        $this->service->deleteAdjustment($id);

        return $this->deleted('Inventory adjustment deleted successfully.');
    }
}
