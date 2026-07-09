<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceOrderResource;
use App\Services\Manufacturing\MaintenanceOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected MaintenanceOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'maintenance_type', 'priority', 'equipment_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($mo) => (new MaintenanceOrderResource($mo))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'maintenance_type' => 'required|in:preventive,corrective',
            'priority' => 'nullable|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_duration' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'checklist' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        $order = $this->service->create($validated);
        return $this->created(new MaintenanceOrderResource($order), 'Maintenance order created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $order = $this->service->find($id);
        return $this->success(new MaintenanceOrderResource($order));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'equipment_id' => 'sometimes|required|exists:equipment,id',
            'maintenance_type' => 'sometimes|required|in:preventive,corrective',
            'priority' => 'nullable|in:low,medium,high,critical',
            'description' => 'nullable|string',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_duration' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'checklist' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);
        $order = $this->service->update($id, $validated);
        return $this->success(new MaintenanceOrderResource($order), 'Maintenance order updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Maintenance order deleted successfully.');
    }

    public function schedule(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'scheduled_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        $order = $this->service->schedule($id, $validated);
        return $this->success(new MaintenanceOrderResource($order), 'Maintenance scheduled.');
    }

    public function startWork(int $id): JsonResponse
    {
        $order = $this->service->startWork($id);
        return $this->success(new MaintenanceOrderResource($order), 'Maintenance work started.');
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'actual_duration' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $order = $this->service->complete($id, $validated);
        return $this->success(new MaintenanceOrderResource($order), 'Maintenance completed.');
    }

    public function cancel(int $id): JsonResponse
    {
        $order = $this->service->cancel($id);
        return $this->success(new MaintenanceOrderResource($order), 'Maintenance order cancelled.');
    }
}
