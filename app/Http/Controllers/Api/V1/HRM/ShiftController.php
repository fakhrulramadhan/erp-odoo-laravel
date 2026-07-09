<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\HRM\ShiftResource;
use App\Services\HRM\ShiftService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ShiftService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($s) => (new ShiftResource($s))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        $shift = $this->service->create($validated);
        return $this->created(new ShiftResource($shift), 'Shift created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $shift = $this->service->find($id);
        return $this->success(new ShiftResource($shift));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i',
            'break_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);
        $shift = $this->service->update($id, $validated);
        return $this->success(new ShiftResource($shift), 'Shift updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Shift deleted successfully.');
    }
}
