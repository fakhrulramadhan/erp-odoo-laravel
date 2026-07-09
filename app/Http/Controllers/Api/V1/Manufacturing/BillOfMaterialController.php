<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillOfMaterialResource;
use App\Services\Manufacturing\BillOfMaterialService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillOfMaterialController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected BillOfMaterialService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'product_id', 'is_default']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($bom) => (new BillOfMaterialResource($bom))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'uom_id' => 'required|exists:unit_of_measures,id',
            'quantity' => 'required|numeric|min:0.0001',
            'routing_id' => 'nullable|exists:routings,id',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'company_id' => 'nullable|exists:companies,id',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.uom_id' => 'required|exists:unit_of_measures,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.scrap_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
        ]);
        $bom = $this->service->create($validated);
        return $this->created(new BillOfMaterialResource($bom), 'Bill of material created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $bom = $this->service->find($id);
        return $this->success(new BillOfMaterialResource($bom));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'uom_id' => 'sometimes|required|exists:unit_of_measures,id',
            'quantity' => 'sometimes|required|numeric|min:0.0001',
            'routing_id' => 'nullable|exists:routings,id',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
            'lines' => 'sometimes|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.uom_id' => 'required|exists:unit_of_measures,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.scrap_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string',
        ]);
        $bom = $this->service->update($id, $validated);
        return $this->success(new BillOfMaterialResource($bom), 'Bill of material updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Bill of material deleted successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $bom = $this->service->approve($id);
        return $this->success(new BillOfMaterialResource($bom), 'Bill of material approved.');
    }

    public function activate(int $id): JsonResponse
    {
        $bom = $this->service->activate($id);
        return $this->success(new BillOfMaterialResource($bom), 'Bill of material activated.');
    }

    public function cancel(int $id): JsonResponse
    {
        $bom = $this->service->cancel($id);
        return $this->success(new BillOfMaterialResource($bom), 'Bill of material cancelled.');
    }

    public function clone(int $id): JsonResponse
    {
        $bom = $this->service->clone($id);
        return $this->created(new BillOfMaterialResource($bom), 'Bill of material cloned successfully.');
    }
}
