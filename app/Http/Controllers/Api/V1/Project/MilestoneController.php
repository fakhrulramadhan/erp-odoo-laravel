<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\MilestoneResource;
use App\Services\Project\MilestoneService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MilestoneService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'project_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($m) => (new MilestoneResource($m))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);
        $milestone = $this->service->create($validated);
        return $this->created(new MilestoneResource($milestone), 'Milestone created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $milestone = $this->service->find($id);
        return $this->success(new MilestoneResource($milestone));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);
        $milestone = $this->service->update($id, $validated);
        return $this->success(new MilestoneResource($milestone), 'Milestone updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Milestone deleted successfully.');
    }
}
