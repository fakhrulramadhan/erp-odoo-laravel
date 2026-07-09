<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\QualityCheckResource;
use App\Services\Manufacturing\QualityCheckService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityCheckController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected QualityCheckService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'product_id', 'source_type', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($qc) => (new QualityCheckResource($qc))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'source_type' => 'nullable|string',
            'source_id' => 'nullable|integer',
            'quantity' => 'required|numeric|min:0.0001',
            'uom_id' => 'nullable|exists:unit_of_measures,id',
            'lot_id' => 'nullable|exists:lots,id',
            'location_id' => 'nullable|exists:locations,id',
            'quarantine_location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.control_point' => 'required|string|max:255',
            'lines.*.method' => 'nullable|string',
            'lines.*.expected_value' => 'nullable|string',
            'lines.*.tolerance_min' => 'nullable|numeric',
            'lines.*.tolerance_max' => 'nullable|numeric',
            'lines.*.notes' => 'nullable|string',
        ]);
        $qc = $this->service->create($validated);
        return $this->created(new QualityCheckResource($qc), 'Quality check created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $qc = $this->service->find($id);
        return $this->success(new QualityCheckResource($qc));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|required|exists:products,id',
            'quantity' => 'sometimes|required|numeric|min:0.0001',
            'uom_id' => 'nullable|exists:unit_of_measures,id',
            'lot_id' => 'nullable|exists:lots,id',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.control_point' => 'required|string|max:255',
            'lines.*.method' => 'nullable|string',
            'lines.*.expected_value' => 'nullable|string',
            'lines.*.tolerance_min' => 'nullable|numeric',
            'lines.*.tolerance_max' => 'nullable|numeric',
            'lines.*.actual_value' => 'nullable|string',
            'lines.*.is_pass' => 'nullable|boolean',
            'lines.*.notes' => 'nullable|string',
        ]);
        $qc = $this->service->update($id, $validated);
        return $this->success(new QualityCheckResource($qc), 'Quality check updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Quality check deleted successfully.');
    }

    public function startInspection(int $id): JsonResponse
    {
        $qc = $this->service->startInspection($id);
        return $this->success(new QualityCheckResource($qc), 'Inspection started.');
    }

    public function pass(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);
        $qc = $this->service->pass($id, $validated);
        return $this->success(new QualityCheckResource($qc), 'Quality check passed.');
    }

    public function fail(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'rejected_qty' => 'nullable|numeric|min:0',
            'defect_type' => 'nullable|string|max:255',
            'corrective_action' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $qc = $this->service->fail($id, $validated);
        return $this->success(new QualityCheckResource($qc), 'Quality check failed.');
    }
}
