<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetResource;
use App\Services\Manufacturing\AssetService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AssetService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'asset_category_id', 'branch_id', 'department_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($a) => (new AssetResource($a))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_number' => 'nullable|string|max:50|unique:assets,asset_number',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'description' => 'nullable|string',
            'acquisition_date' => 'required|date',
            'acquisition_cost' => 'required|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'useful_life_months' => 'required|integer|min:1',
            'depreciation_method' => 'required|string',
            'start_depreciation_date' => 'nullable|date',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'location' => 'nullable|string|max:255',
            'custodian_id' => 'nullable|exists:users,id',
            'serial_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $asset = $this->service->create($validated);
        return $this->created(new AssetResource($asset), 'Asset created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $asset = $this->service->find($id);
        return $this->success(new AssetResource($asset));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'asset_number' => "nullable|string|max:50|unique:assets,asset_number,{$id}",
            'asset_category_id' => 'sometimes|required|exists:asset_categories,id',
            'description' => 'nullable|string',
            'acquisition_date' => 'sometimes|required|date',
            'acquisition_cost' => 'sometimes|required|numeric|min:0',
            'residual_value' => 'nullable|numeric|min:0',
            'useful_life_months' => 'sometimes|required|integer|min:1',
            'depreciation_method' => 'sometimes|required|string',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'location' => 'nullable|string|max:255',
            'custodian_id' => 'nullable|exists:users,id',
            'serial_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $asset = $this->service->update($id, $validated);
        return $this->success(new AssetResource($asset), 'Asset updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Asset deleted successfully.');
    }

    public function activate(int $id): JsonResponse
    {
        $asset = $this->service->activate($id);
        return $this->success(new AssetResource($asset), 'Asset activated.');
    }

    public function transfer(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'to_branch_id' => 'required|exists:branches,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'to_location' => 'nullable|string|max:255',
            'to_custodian_id' => 'nullable|exists:users,id',
            'transfer_date' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);
        $transfer = $this->service->transfer($id, $validated);
        return $this->success($transfer, 'Asset transferred successfully.');
    }

    public function dispose(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'disposal_amount' => 'nullable|numeric|min:0',
            'disposal_reason' => 'nullable|string',
        ]);
        $asset = $this->service->dispose($id, $validated);
        return $this->success(new AssetResource($asset), 'Asset disposed.');
    }

    public function depreciate(int $id): JsonResponse
    {
        $asset = $this->service->depreciate($id);
        return $this->success(new AssetResource($asset), 'Depreciation recorded.');
    }
}
