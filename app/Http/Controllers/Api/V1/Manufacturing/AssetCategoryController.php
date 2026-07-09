<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\AssetCategoryResource;
use App\Services\Manufacturing\AssetCategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AssetCategoryService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($ac) => (new AssetCategoryResource($ac))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:asset_categories,code',
            'description' => 'nullable|string',
            'asset_account_id' => 'nullable|exists:accounts,id',
            'depreciation_expense_account_id' => 'nullable|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:accounts,id',
            'disposal_account_id' => 'nullable|exists:accounts,id',
            'default_useful_life_months' => 'nullable|integer|min:1',
            'default_depreciation_method' => 'nullable|string',
            'default_residual_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        $category = $this->service->create($validated);
        return $this->created(new AssetCategoryResource($category), 'Asset category created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $category = $this->service->find($id);
        return $this->success(new AssetCategoryResource($category));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => "nullable|string|max:50|unique:asset_categories,code,{$id}",
            'description' => 'nullable|string',
            'asset_account_id' => 'nullable|exists:accounts,id',
            'depreciation_expense_account_id' => 'nullable|exists:accounts,id',
            'accumulated_depreciation_account_id' => 'nullable|exists:accounts,id',
            'disposal_account_id' => 'nullable|exists:accounts,id',
            'default_useful_life_months' => 'nullable|integer|min:1',
            'default_depreciation_method' => 'nullable|string',
            'default_residual_rate' => 'nullable|numeric|min:0|max:100',
        ]);
        $category = $this->service->update($id, $validated);
        return $this->success(new AssetCategoryResource($category), 'Asset category updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Asset category deleted successfully.');
    }
}
