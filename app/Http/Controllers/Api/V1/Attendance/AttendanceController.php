<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Services\Attendance\AttendanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AttendanceService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'employee_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($a) => (new AttendanceResource($a))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'shift_id' => 'nullable|exists:shifts,id',
            'notes' => 'nullable|string',
        ]);
        $attendance = $this->service->create($validated);
        return $this->created(new AttendanceResource($attendance), 'Attendance created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $attendance = $this->service->find($id);
        return $this->success(new AttendanceResource($attendance));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);
        $attendance = $this->service->update($id, $validated);
        return $this->success(new AttendanceResource($attendance), 'Attendance updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Attendance deleted successfully.');
    }

    public function checkIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'check_in_time' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $attendance = $this->service->checkIn($validated);
        return $this->created(new AttendanceResource($attendance), 'Check-in recorded.');
    }

    public function checkOut(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'check_out_time' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $attendance = $this->service->checkOut($id, $validated);
        return $this->success(new AttendanceResource($attendance), 'Check-out recorded.');
    }
}
