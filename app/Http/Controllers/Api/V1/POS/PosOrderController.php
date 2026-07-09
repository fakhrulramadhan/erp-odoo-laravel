<?php

namespace App\Http\Controllers\Api\V1\POS;

use App\Http\Controllers\Controller;
use App\Http\Resources\POS\PosOrderResource;
use App\Services\POS\PosOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PosOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'session_id', 'customer_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($o) => (new PosOrderResource($o))->resolve($request));
        return $this->paginated($paginator);
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->service->find($id);
        return $this->success(new PosOrderResource($order));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('POS order deleted successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:pos_sessions,id',
            'customer_id' => 'nullable|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'currency_id' => 'required|exists:currencies,id',
            'order_date' => 'required|date',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
        ]);
        $order = $this->service->create($validated);
        return $this->created(new PosOrderResource($order), 'POS order created successfully.');
    }

    public function refund(int $id): JsonResponse
    {
        $order = $this->service->refund($id);
        return $this->success(new PosOrderResource($order), 'POS order refunded.');
    }
}
