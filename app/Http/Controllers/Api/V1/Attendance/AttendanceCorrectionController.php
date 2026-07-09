<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\AttendanceCorrectionResource;
use App\Services\Attendance\AttendanceCorrectionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AttendanceCorrectionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'attendance_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($c) => (new AttendanceCorrectionResource($c))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'requested_check_in' => 'nullable|date',
            'requested_check_out' => 'nullable|date',
            'reason' => 'required|string',
        ]);
        $correction = $this->service->create($validated);
        return $this->created(new AttendanceCorrectionResource($correction), 'Correction request created.');
    }

    public function show(int $id): JsonResponse
    {
        $correction = $this->service->find($id);
        return $this->success(new AttendanceCorrectionResource($correction));
    }

    public function approve(int $id): JsonResponse
    {
        $correction = $this->service->approve($id);
        return $this->success(new AttendanceCorrectionResource($correction), 'Correction approved.');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['rejection_reason' => 'nullable|string']);
        $correction = $this->service->reject($id, $validated['rejection_reason'] ?? null);
        return $this->success(new AttendanceCorrectionResource($correction), 'Correction rejected.');
    }
}
