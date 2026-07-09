<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\ProjectResource;
use App\Services\Project\ProjectService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProjectService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'company_id', 'manager_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($p) => (new ProjectResource($p))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_id' => 'required|exists:companies,id',
            'manager_id' => 'nullable|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'nullable|string|max:50',
        ]);
        $project = $this->service->create($validated);
        return $this->created(new ProjectResource($project), 'Project created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $project = $this->service->find($id);
        return $this->success(new ProjectResource($project));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'customer_id' => 'nullable|exists:customers,id',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'budget' => 'nullable|numeric|min:0',
        ]);
        $project = $this->service->update($id, $validated);
        return $this->success(new ProjectResource($project), 'Project updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Project deleted successfully.');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|string|max:50']);
        $project = $this->service->updateStatus($id, $validated['status']);
        return $this->success(new ProjectResource($project), 'Project status updated.');
    }
}
