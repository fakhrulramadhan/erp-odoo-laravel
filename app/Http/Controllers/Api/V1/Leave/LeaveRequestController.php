<?php

namespace App\Http\Controllers\Api\V1\Leave;

use App\Http\Controllers\Controller;
use App\Http\Resources\Leave\LeaveRequestResource;
use App\Services\Leave\LeaveRequestService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeaveRequestService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'employee_id', 'leave_type_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($lr) => (new LeaveRequestResource($lr))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);
        $lr = $this->service->create($validated);
        return $this->created(new LeaveRequestResource($lr), 'Leave request created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $lr = $this->service->find($id);
        return $this->success(new LeaveRequestResource($lr));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);
        $lr = $this->service->update($id, $validated);
        return $this->success(new LeaveRequestResource($lr), 'Leave request updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Leave request deleted successfully.');
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['comment' => 'nullable|string']);
        $lr = $this->service->approve($id, $validated['comment'] ?? null);
        return $this->success(new LeaveRequestResource($lr), 'Leave request approved.');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['comment' => 'nullable|string']);
        $lr = $this->service->reject($id, $validated['comment'] ?? null);
        return $this->success(new LeaveRequestResource($lr), 'Leave request rejected.');
    }

    public function cancel(int $id): JsonResponse
    {
        $lr = $this->service->cancel($id);
        return $this->success(new LeaveRequestResource($lr), 'Leave request cancelled.');
    }
}
