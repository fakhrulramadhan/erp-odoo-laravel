<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseOrderResource;
use App\Services\Purchasing\PurchaseOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PurchaseOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'vendor_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);

        $paginator = $this->service->list($filters, $perPage);

        // Transform to resource on the collection
        $paginator->getCollection()->transform(fn($po) => (new PurchaseOrderResource($po))->resolve($request));

        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'company_id' => 'required|exists:companies,id',
            'currency_id' => 'required|exists:currencies,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.tax_id' => 'nullable|exists:tax_settings,id',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
            'lines.*.delivery_date' => 'nullable|date',
        ]);

        $po = $this->service->create($validated);

        return $this->created(new PurchaseOrderResource($po), 'Purchase order created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $po = $this->service->find($id);

        return $this->success(new PurchaseOrderResource($po));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'order_date' => 'sometimes|required|date',
            'expected_date' => 'nullable|date',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.tax_id' => 'nullable|exists:tax_settings,id',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
            'lines.*.delivery_date' => 'nullable|date',
        ]);

        $po = $this->service->update($id, $validated);

        return $this->success(new PurchaseOrderResource($po), 'Purchase order updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->deleted('Purchase order deleted successfully.');
    }

    public function submitForApproval(int $id): JsonResponse
    {
        $po = $this->service->submitForApproval($id);

        return $this->success(new PurchaseOrderResource($po), 'Purchase order submitted for approval.');
    }

    public function approve(int $id): JsonResponse
    {
        $po = $this->service->approve($id);

        return $this->success(new PurchaseOrderResource($po), 'Purchase order approved.');
    }

    public function cancel(int $id): JsonResponse
    {
        $po = $this->service->cancel($id);

        return $this->success(new PurchaseOrderResource($po), 'Purchase order cancelled.');
    }

    public function sendToVendor(int $id): JsonResponse
    {
        $po = $this->service->sendToVendor($id);

        return $this->success(new PurchaseOrderResource($po), 'Purchase order sent to vendor.');
    }
}
