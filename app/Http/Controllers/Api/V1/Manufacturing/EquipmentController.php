<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipmentResource;
use App\Services\Manufacturing\EquipmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EquipmentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'work_center_id', 'branch_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($e) => (new EquipmentResource($e))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:equipment,code',
            'description' => 'nullable|string',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'asset_id' => 'nullable|exists:assets,id',
            'branch_id' => 'nullable|exists:branches,id',
            'location' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $equipment = $this->service->create($validated);
        return $this->created(new EquipmentResource($equipment), 'Equipment created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $equipment = $this->service->find($id);
        return $this->success(new EquipmentResource($equipment));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => "nullable|string|max:50|unique:equipment,code,{$id}",
            'description' => 'nullable|string',
            'work_center_id' => 'nullable|exists:work_centers,id',
            'asset_id' => 'nullable|exists:assets,id',
            'branch_id' => 'nullable|exists:branches,id',
            'location' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'maintenance_interval_days' => 'nullable|integer|min:1',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $equipment = $this->service->update($id, $validated);
        return $this->success(new EquipmentResource($equipment), 'Equipment updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Equipment deleted successfully.');
    }
}
