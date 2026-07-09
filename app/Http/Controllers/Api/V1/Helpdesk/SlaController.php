<?php

namespace App\Http\Controllers\Api\V1\Helpdesk;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helpdesk\SlaResource;
use App\Services\Helpdesk\SlaService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlaController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SlaService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($sla) => (new SlaResource($sla))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'priority' => 'required|string|max:20',
            'response_hours' => 'nullable|integer|min:0',
            'resolution_hours' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $sla = $this->service->create($validated);
        return $this->created(new SlaResource($sla), 'SLA created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $sla = $this->service->find($id);
        return $this->success(new SlaResource($sla));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'priority' => 'sometimes|required|string|max:20',
            'response_hours' => 'nullable|integer|min:0',
            'resolution_hours' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $sla = $this->service->update($id, $validated);
        return $this->success(new SlaResource($sla), 'SLA updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('SLA deleted successfully.');
    }
}
