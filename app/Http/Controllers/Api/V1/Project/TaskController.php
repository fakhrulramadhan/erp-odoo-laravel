<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\TaskResource;
use App\Services\Project\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TaskService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'priority', 'project_id', 'assigned_to', 'sprint_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($t) => (new TaskResource($t))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|max:20',
            'sprint_id' => 'nullable|exists:sprints,id',
            'milestone_id' => 'nullable|exists:milestones,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);
        $task = $this->service->create($validated);
        return $this->created(new TaskResource($task), 'Task created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $task = $this->service->find($id);
        return $this->success(new TaskResource($task));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'nullable|string|max:20',
            'sprint_id' => 'nullable|exists:sprints,id',
            'milestone_id' => 'nullable|exists:milestones,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);
        $task = $this->service->update($id, $validated);
        return $this->success(new TaskResource($task), 'Task updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Task deleted successfully.');
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|string|max:50']);
        $task = $this->service->updateStatus($id, $validated['status']);
        return $this->success(new TaskResource($task), 'Task status updated.');
    }

    public function addComment(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'attachment_path' => 'nullable|string|max:500',
        ]);
        $task = $this->service->addComment($id, $validated);
        return $this->success(new TaskResource($task), 'Comment added.');
    }
}
