<?php

namespace App\Http\Controllers\Api\V1\Sales;

use App\Http\Controllers\Controller;
use App\Http\Resources\Finance\InvoiceResource;
use App\Http\Resources\Sales\SalesOrderResource;
use App\Services\Sales\SalesOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SalesOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'customer_id', 'date_from', 'date_to']);
        $paginator = $this->service->list($filters, $request->input('per_page', 15));
        $paginator->getCollection()->transform(fn ($so) => (new SalesOrderResource($so))->resolve($request));

        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'company_id' => 'nullable|exists:companies,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
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

        $order = $this->service->create($validated);

        return $this->created(new SalesOrderResource($order), 'Sales order created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new SalesOrderResource($this->service->find($id)));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'order_date' => 'sometimes|required|date',
            'delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.tax_id' => 'nullable|exists:tax_settings,id',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
        ]);

        $order = $this->service->update($id, $validated);

        return $this->success(new SalesOrderResource($order), 'Sales order updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $order = $this->service->find($id);

        if ($order->status->value !== 'draft' && $order->status->value !== 'cancelled') {
            throw new \DomainException('Only draft or cancelled sales orders can be deleted.');
        }

        $order->delete();

        return $this->deleted('Sales order deleted successfully.');
    }

    public function confirm(int $id): JsonResponse
    {
        return $this->success(new SalesOrderResource($this->service->confirm($id)), 'Sales order confirmed.');
    }

    public function cancel(int $id): JsonResponse
    {
        return $this->success(new SalesOrderResource($this->service->cancel($id)), 'Sales order cancelled.');
    }

    public function createDelivery(int $id): JsonResponse
    {
        return $this->success(new SalesOrderResource($this->service->createDelivery($id)), 'Delivery created for sales order.');
    }

    public function createInvoice(int $id): JsonResponse
    {
        $invoice = $this->service->createInvoice($id);

        return $this->success(new InvoiceResource($invoice), 'Customer invoice created and posted.');
    }
}
