<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierQuotationResource;
use App\Services\Purchasing\SupplierQuotationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierQuotationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SupplierQuotationService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'vendor_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);

        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($q) => (new SupplierQuotationResource($q))->resolve($request));

        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'company_id' => 'required|exists:companies,id',
            'currency_id' => 'required|exists:currencies,id',
            'quotation_date' => 'required|date',
            'validity_date' => 'nullable|date|after_or_equal:quotation_date',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.discount_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
        ]);

        $quotation = $this->service->create($validated);

        return $this->created(new SupplierQuotationResource($quotation), 'Supplier quotation created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $quotation = $this->service->find($id);

        return $this->success(new SupplierQuotationResource($quotation));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'quotation_date' => 'sometimes|required|date',
            'validity_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
        ]);

        $quotation = $this->service->update($id, $validated);

        return $this->success(new SupplierQuotationResource($quotation), 'Supplier quotation updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->deleted('Supplier quotation deleted successfully.');
    }

    public function sendToVendor(int $id): JsonResponse
    {
        $quotation = $this->service->sendToVendor($id);

        return $this->success(new SupplierQuotationResource($quotation), 'Quotation sent to vendor.');
    }

    public function accept(int $id): JsonResponse
    {
        $quotation = $this->service->markAsAccepted($id);

        return $this->success(new SupplierQuotationResource($quotation), 'Quotation accepted.');
    }

    public function reject(int $id): JsonResponse
    {
        $quotation = $this->service->markAsRejected($id);

        return $this->success(new SupplierQuotationResource($quotation), 'Quotation rejected.');
    }
}
