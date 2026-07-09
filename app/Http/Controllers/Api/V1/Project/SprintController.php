<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\SprintResource;
use App\Services\Project\SprintService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SprintService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'project_id', 'status']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($s) => (new SprintResource($s))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'goal' => 'nullable|string',
        ]);
        $sprint = $this->service->create($validated);
        return $this->created(new SprintResource($sprint), 'Sprint created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $sprint = $this->service->find($id);
        return $this->success(new SprintResource($sprint));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'goal' => 'nullable|string',
        ]);
        $sprint = $this->service->update($id, $validated);
        return $this->success(new SprintResource($sprint), 'Sprint updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Sprint deleted successfully.');
    }

    public function start(int $id): JsonResponse
    {
        $sprint = $this->service->start($id);
        return $this->success(new SprintResource($sprint), 'Sprint started.');
    }

    public function complete(int $id): JsonResponse
    {
        $sprint = $this->service->complete($id);
        return $this->success(new SprintResource($sprint), 'Sprint completed.');
    }
}
