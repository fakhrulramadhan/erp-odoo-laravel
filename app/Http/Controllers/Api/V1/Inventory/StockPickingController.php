<?php

namespace App\Http\Controllers\Api\V1\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockPickingResource;
use App\Services\Inventory\StockPickingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockPickingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected StockPickingService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'picking_type', 'status', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);

        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($p) => (new StockPickingResource($p))->resolve($request));

        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'picking_type' => 'required|in:incoming,outgoing,internal,dropship',
            'company_id' => 'nullable|exists:companies,id',
            'source_location_id' => 'nullable|exists:stock_locations,id',
            'destination_location_id' => 'nullable|exists:stock_locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'customer_id' => 'nullable|exists:customers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'moves' => 'required|array|min:1',
            'moves.*.product_id' => 'required|exists:products,id',
            'moves.*.quantity' => 'required|numeric|min:0.0001',
            'moves.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'moves.*.source_location_id' => 'nullable|exists:stock_locations,id',
            'moves.*.destination_location_id' => 'nullable|exists:stock_locations,id',
            'moves.*.lot_id' => 'nullable|exists:lots,id',
            'moves.*.unit_cost' => 'nullable|numeric|min:0',
            'moves.*.notes' => 'nullable|string',
        ]);

        // Auto-fill company_id from authenticated user if not provided
        if (empty($validated['company_id'])) {
            $userId = auth()->id();
            $validated['company_id'] = $userId ? \App\Models\User::find($userId)?->company_id ?? 1 : 1;
        }

        $picking = $this->service->create($validated);

        return $this->created(new StockPickingResource($picking), 'Stock picking created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $picking = $this->service->find($id);

        return $this->success(new StockPickingResource($picking));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'source_location_id' => 'nullable|exists:stock_locations,id',
            'destination_location_id' => 'nullable|exists:stock_locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'customer_id' => 'nullable|exists:customers,id',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'moves' => 'sometimes|array|min:1',
            'moves.*.product_id' => 'required|exists:products,id',
            'moves.*.quantity' => 'required|numeric|min:0.0001',
            'moves.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'moves.*.source_location_id' => 'nullable|exists:stock_locations,id',
            'moves.*.destination_location_id' => 'nullable|exists:stock_locations,id',
            'moves.*.lot_id' => 'nullable|exists:lots,id',
            'moves.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $picking = $this->service->update($id, $validated);

        return $this->success(new StockPickingResource($picking), 'Stock picking updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->deleted('Stock picking deleted successfully.');
    }

    public function confirm(int $id): JsonResponse
    {
        $picking = $this->service->confirm($id);

        return $this->success(new StockPickingResource($picking), 'Stock picking confirmed.');
    }

    public function validate_picking(int $id): JsonResponse
    {
        $picking = $this->service->validate($id);

        return $this->success(new StockPickingResource($picking), 'Stock picking validated. Stock has been updated.');
    }

    public function cancel(int $id): JsonResponse
    {
        $picking = $this->service->cancel($id);

        return $this->success(new StockPickingResource($picking), 'Stock picking cancelled.');
    }
}
