<?php

namespace App\Http\Controllers\Api\V1\Purchasing;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseOrderResource;
use App\Services\Purchasing\PurchaseRequisitionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PurchaseRequisitionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PurchaseRequisitionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'department_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);

        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($r) => (new \App\Http\Resources\PurchaseRequisitionResource($r))->resolve($request));

        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'currency_id' => 'required|exists:currencies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'requisition_date' => 'required|date',
            'required_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.estimated_price' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string',
            'lines.*.required_date' => 'nullable|date',
        ]);

        $requisition = $this->service->create($validated);

        return $this->created(\App\Http\Resources\PurchaseRequisitionResource::make($requisition), 'Purchase requisition created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $requisition = $this->service->find($id);

        return $this->success(\App\Http\Resources\PurchaseRequisitionResource::make($requisition));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'requisition_date' => 'sometimes|required|date',
            'required_date' => 'nullable|date',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.uom_id' => 'nullable|exists:unit_of_measures,id',
            'lines.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        $requisition = $this->service->update($id, $validated);

        return $this->success(\App\Http\Resources\PurchaseRequisitionResource::make($requisition), 'Purchase requisition updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return $this->deleted('Purchase requisition deleted successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $requisition = $this->service->approve($id);

        return $this->success(\App\Http\Resources\PurchaseRequisitionResource::make($requisition), 'Purchase requisition approved.');
    }

    public function cancel(int $id): JsonResponse
    {
        $requisition = $this->service->cancel($id);

        return $this->success(\App\Http\Resources\PurchaseRequisitionResource::make($requisition), 'Purchase requisition cancelled.');
    }
}
