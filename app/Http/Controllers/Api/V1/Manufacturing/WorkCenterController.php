<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkCenterResource;
use App\Services\Manufacturing\WorkCenterService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkCenterController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WorkCenterService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active', 'branch_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($wc) => (new WorkCenterResource($wc))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:work_centers,code',
            'description' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'capacity_per_hour' => 'nullable|numeric|min:0',
            'cost_per_hour' => 'nullable|numeric|min:0',
            'efficiency' => 'nullable|numeric|min:0|max:200',
            'is_active' => 'nullable|boolean',
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
        ]);
        $wc = $this->service->create($validated);
        return $this->created(new WorkCenterResource($wc), 'Work center created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $wc = $this->service->find($id);
        return $this->success(new WorkCenterResource($wc));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => "nullable|string|max:50|unique:work_centers,code,{$id}",
            'description' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'capacity_per_hour' => 'nullable|numeric|min:0',
            'cost_per_hour' => 'nullable|numeric|min:0',
            'efficiency' => 'nullable|numeric|min:0|max:200',
            'is_active' => 'nullable|boolean',
            'operators' => 'nullable|array',
            'operators.*' => 'exists:users,id',
        ]);
        $wc = $this->service->update($id, $validated);
        return $this->success(new WorkCenterResource($wc), 'Work center updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Work center deleted successfully.');
    }
}
