<?php

namespace App\Http\Controllers\Api\V1\Project;

use App\Http\Controllers\Controller;
use App\Http\Resources\Project\TimesheetResource;
use App\Services\Project\TimesheetService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TimesheetService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'project_id', 'task_id', 'user_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($t) => (new TimesheetResource($t))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_id' => 'nullable|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);
        $ts = $this->service->create($validated);
        return $this->created(new TimesheetResource($ts), 'Timesheet created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $ts = $this->service->find($id);
        return $this->success(new TimesheetResource($ts));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'hours' => 'sometimes|required|numeric|min:0.01',
            'description' => 'nullable|string',
        ]);
        $ts = $this->service->update($id, $validated);
        return $this->success(new TimesheetResource($ts), 'Timesheet updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Timesheet deleted successfully.');
    }

    public function approve(int $id): JsonResponse
    {
        $ts = $this->service->approve($id);
        return $this->success(new TimesheetResource($ts), 'Timesheet approved.');
    }
}
