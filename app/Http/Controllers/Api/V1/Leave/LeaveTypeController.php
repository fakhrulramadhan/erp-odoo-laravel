<?php

namespace App\Http\Controllers\Api\V1\Leave;

use App\Http\Controllers\Controller;
use App\Http\Resources\Leave\LeaveTypeResource;
use App\Services\Leave\LeaveTypeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeaveTypeService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($lt) => (new LeaveTypeResource($lt))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'max_days_per_year' => 'nullable|integer|min:0',
            'is_paid' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        $lt = $this->service->create($validated);
        return $this->created(new LeaveTypeResource($lt), 'Leave type created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $lt = $this->service->find($id);
        return $this->success(new LeaveTypeResource($lt));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'max_days_per_year' => 'nullable|integer|min:0',
            'is_paid' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        $lt = $this->service->update($id, $validated);
        return $this->success(new LeaveTypeResource($lt), 'Leave type updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Leave type deleted successfully.');
    }
}
