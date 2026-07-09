<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoutingResource;
use App\Services\Manufacturing\RoutingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoutingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RoutingService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($r) => (new RoutingResource($r))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'operations' => 'nullable|array',
            'operations.*.name' => 'required|string|max:255',
            'operations.*.work_center_id' => 'required|exists:work_centers,id',
            'operations.*.duration_minutes' => 'required|numeric|min:1',
            'operations.*.setup_time_minutes' => 'nullable|numeric|min:0',
            'operations.*.cost_per_hour' => 'nullable|numeric|min:0',
            'operations.*.description' => 'nullable|string',
        ]);
        $routing = $this->service->create($validated);
        return $this->created(new RoutingResource($routing), 'Routing created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $routing = $this->service->find($id);
        return $this->success(new RoutingResource($routing));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'operations' => 'nullable|array',
            'operations.*.name' => 'required|string|max:255',
            'operations.*.work_center_id' => 'required|exists:work_centers,id',
            'operations.*.duration_minutes' => 'required|numeric|min:1',
            'operations.*.setup_time_minutes' => 'nullable|numeric|min:0',
            'operations.*.cost_per_hour' => 'nullable|numeric|min:0',
            'operations.*.description' => 'nullable|string',
        ]);
        $routing = $this->service->update($id, $validated);
        return $this->success(new RoutingResource($routing), 'Routing updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Routing deleted successfully.');
    }
}
