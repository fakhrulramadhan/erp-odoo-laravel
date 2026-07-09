<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\ManufacturingOrderResource;
use App\Services\Manufacturing\ManufacturingOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManufacturingOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ManufacturingOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'product_id', 'work_center_id', 'bom_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($mo) => (new ManufacturingOrderResource($mo))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'bom_id' => 'required|exists:bill_of_materials,id',
            'routing_id' => 'nullable|exists:routings,id',
            'quantity' => 'required|numeric|min:0.0001',
            'uom_id' => 'required|exists:unit_of_measures,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'source_location_id' => 'nullable|exists:locations,id',
            'destination_location_id' => 'nullable|exists:locations,id',
            'planned_start' => 'nullable|date',
            'planned_finish' => 'nullable|date|after_or_equal:planned_start',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|integer|min:0|max:10',
            'notes' => 'nullable|string',
            'sale_order_id' => 'nullable|exists:sale_orders,id',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.line_type' => 'required|in:component,byproduct',
            'lines.*.source_location_id' => 'nullable|exists:locations,id',
        ]);
        $mo = $this->service->create($validated);
        return $this->created(new ManufacturingOrderResource($mo), 'Manufacturing order created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $mo = $this->service->find($id);
        return $this->success(new ManufacturingOrderResource($mo));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'bom_id' => 'sometimes|required|exists:bill_of_materials,id',
            'routing_id' => 'nullable|exists:routings,id',
            'quantity' => 'sometimes|required|numeric|min:0.0001',
            'uom_id' => 'sometimes|required|exists:unit_of_measures,id',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'source_location_id' => 'nullable|exists:locations,id',
            'destination_location_id' => 'nullable|exists:locations,id',
            'planned_start' => 'nullable|date',
            'planned_finish' => 'nullable|date',
            'deadline' => 'nullable|date',
            'priority' => 'nullable|integer|min:0|max:10',
            'notes' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.line_type' => 'required|in:component,byproduct',
            'lines.*.source_location_id' => 'nullable|exists:locations,id',
        ]);
        $mo = $this->service->update($id, $validated);
        return $this->success(new ManufacturingOrderResource($mo), 'Manufacturing order updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Manufacturing order deleted successfully.');
    }

    public function confirm(int $id): JsonResponse
    {
        $mo = $this->service->confirm($id);
        return $this->success(new ManufacturingOrderResource($mo), 'Manufacturing order confirmed.');
    }

    public function reserveMaterials(int $id): JsonResponse
    {
        $mo = $this->service->reserveMaterials($id);
        return $this->success(new ManufacturingOrderResource($mo), 'Materials reserved successfully.');
    }

    public function startProduction(int $id): JsonResponse
    {
        $mo = $this->service->startProduction($id);
        return $this->success(new ManufacturingOrderResource($mo), 'Production started.');
    }

    public function finishProduction(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'produced_qty' => 'nullable|numeric|min:0',
            'scrap_qty' => 'nullable|numeric|min:0',
        ]);
        $mo = $this->service->finishProduction($id, $validated);
        return $this->success(new ManufacturingOrderResource($mo), 'Production finished.');
    }

    public function markFinished(int $id): JsonResponse
    {
        $mo = $this->service->markFinished($id);
        return $this->success(new ManufacturingOrderResource($mo), 'Manufacturing order marked as finished.');
    }

    public function close(int $id): JsonResponse
    {
        $mo = $this->service->close($id);
        return $this->success(new ManufacturingOrderResource($mo), 'Manufacturing order closed.');
    }

    public function cancel(int $id): JsonResponse
    {
        $mo = $this->service->cancel($id);
        return $this->success(new ManufacturingOrderResource($mo), 'Manufacturing order cancelled.');
    }
}
