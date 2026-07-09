<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\HRM\WorkScheduleResource;
use App\Services\HRM\WorkScheduleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WorkScheduleService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($ws) => (new WorkScheduleResource($ws))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'monday' => 'nullable|boolean',
            'tuesday' => 'nullable|boolean',
            'wednesday' => 'nullable|boolean',
            'thursday' => 'nullable|boolean',
            'friday' => 'nullable|boolean',
            'saturday' => 'nullable|boolean',
            'sunday' => 'nullable|boolean',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);
        $ws = $this->service->create($validated);
        return $this->created(new WorkScheduleResource($ws), 'Work schedule created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $ws = $this->service->find($id);
        return $this->success(new WorkScheduleResource($ws));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'monday' => 'nullable|boolean',
            'tuesday' => 'nullable|boolean',
            'wednesday' => 'nullable|boolean',
            'thursday' => 'nullable|boolean',
            'friday' => 'nullable|boolean',
            'saturday' => 'nullable|boolean',
            'sunday' => 'nullable|boolean',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
        ]);
        $ws = $this->service->update($id, $validated);
        return $this->success(new WorkScheduleResource($ws), 'Work schedule updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Work schedule deleted successfully.');
    }
}
