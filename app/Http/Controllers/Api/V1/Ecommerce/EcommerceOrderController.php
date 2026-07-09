<?php

namespace App\Http\Controllers\Api\V1\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\EcommerceOrderResource;
use App\Services\Ecommerce\EcommerceOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcommerceOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EcommerceOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'customer_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($o) => (new EcommerceOrderResource($o))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'currency_id' => 'required|exists:currencies,id',
            'shipping_method_id' => 'nullable|exists:shipping_methods,id',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.ecommerce_product_id' => 'required|exists:ecommerce_products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        $order = $this->service->create($validated);
        return $this->created(new EcommerceOrderResource($order), 'E-commerce order created.');
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->service->find($id);
        return $this->success(new EcommerceOrderResource($order));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $order = $this->service->update($id, $validated);
        return $this->success(new EcommerceOrderResource($order), 'E-commerce order updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('E-commerce order deleted.');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|string|max:50']);
        $order = $this->service->updateStatus($id, $validated['status']);
        return $this->success(new EcommerceOrderResource($order), 'Order status updated.');
    }
}
