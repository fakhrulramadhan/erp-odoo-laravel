<?php

namespace App\Http\Controllers\Api\V1\BI;

use App\Http\Controllers\Controller;
use App\Http\Resources\BI\DashboardResource;
use App\Services\BI\DashboardService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DashboardService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'company_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($d) => (new DashboardResource($d))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'company_id' => 'nullable|exists:companies,id',
            'is_default' => 'nullable|boolean',
            'widgets' => 'nullable|array',
            'widgets.*.name' => 'required|string|max:255',
            'widgets.*.widget_type' => 'required|string|max:50',
            'widgets.*.config' => 'nullable|array',
            'widgets.*.position_x' => 'nullable|integer|min:0',
            'widgets.*.position_y' => 'nullable|integer|min:0',
            'widgets.*.width' => 'nullable|integer|min:1',
            'widgets.*.height' => 'nullable|integer|min:1',
        ]);
        $dashboard = $this->service->create($validated);
        return $this->created(new DashboardResource($dashboard), 'Dashboard created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $dashboard = $this->service->find($id);
        return $this->success(new DashboardResource($dashboard));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);
        $dashboard = $this->service->update($id, $validated);
        return $this->success(new DashboardResource($dashboard), 'Dashboard updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Dashboard deleted successfully.');
    }
}
