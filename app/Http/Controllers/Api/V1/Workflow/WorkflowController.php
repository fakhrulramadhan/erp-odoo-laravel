<?php

namespace App\Http\Controllers\Api\V1\Workflow;

use App\Http\Controllers\Controller;
use App\Http\Resources\Workflow\WorkflowResource;
use App\Services\Workflow\WorkflowService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WorkflowService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($w) => (new WorkflowResource($w))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string|max:100',
            'description' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*.name' => 'required|string|max:255',
            'steps.*.step_type' => 'nullable|string|max:50',
            'steps.*.assigned_to' => 'nullable|exists:users,id',
            'steps.*.assigned_role' => 'nullable|string|max:50',
        ]);
        $workflow = $this->service->create($validated);
        return $this->created(new WorkflowResource($workflow), 'Workflow created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $workflow = $this->service->find($id);
        return $this->success(new WorkflowResource($workflow));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'module' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
        ]);
        $workflow = $this->service->update($id, $validated);
        return $this->success(new WorkflowResource($workflow), 'Workflow updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Workflow deleted successfully.');
    }

    public function activate(int $id): JsonResponse
    {
        $workflow = $this->service->activate($id);
        return $this->success(new WorkflowResource($workflow), 'Workflow activated.');
    }

    public function deactivate(int $id): JsonResponse
    {
        $workflow = $this->service->deactivate($id);
        return $this->success(new WorkflowResource($workflow), 'Workflow deactivated.');
    }
}
