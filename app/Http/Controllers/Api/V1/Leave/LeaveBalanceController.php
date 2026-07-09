<?php

namespace App\Http\Controllers\Api\V1\Leave;

use App\Http\Controllers\Controller;
use App\Http\Resources\Leave\LeaveBalanceResource;
use App\Services\Leave\LeaveBalanceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeaveBalanceService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'employee_id', 'leave_type_id', 'year']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($lb) => (new LeaveBalanceResource($lb))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer',
            'total_days' => 'required|numeric|min:0',
            'used_days' => 'nullable|numeric|min:0',
        ]);
        $lb = $this->service->create($validated);
        return $this->created(new LeaveBalanceResource($lb), 'Leave balance created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $lb = $this->service->find($id);
        return $this->success(new LeaveBalanceResource($lb));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'total_days' => 'sometimes|required|numeric|min:0',
            'used_days' => 'nullable|numeric|min:0',
        ]);
        $lb = $this->service->update($id, $validated);
        return $this->success(new LeaveBalanceResource($lb), 'Leave balance updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Leave balance deleted successfully.');
    }

    public function initializeYear(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'leave_type_id' => 'nullable|exists:leave_types,id',
        ]);
        $count = $this->service->initializeYear($validated['year'], $validated['leave_type_id'] ?? null);
        return $this->success(['initialized' => $count], "Leave balances initialized for {$count} employees.");
    }
}
