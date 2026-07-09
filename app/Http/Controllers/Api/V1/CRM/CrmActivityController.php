<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\CrmActivityResource;
use App\Services\CRM\CrmActivityService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrmActivityController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CrmActivityService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'type', 'lead_id', 'opportunity_id', 'user_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($a) => (new CrmActivityResource($a))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:50',
            'lead_id' => 'nullable|exists:leads,id',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'user_id' => 'nullable|exists:users,id',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $activity = $this->service->create($validated);
        return $this->created(new CrmActivityResource($activity), 'Activity created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $activity = $this->service->find($id);
        return $this->success(new CrmActivityResource($activity));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|required|string|max:50',
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $activity = $this->service->update($id, $validated);
        return $this->success(new CrmActivityResource($activity), 'Activity updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Activity deleted successfully.');
    }

    public function complete(int $id): JsonResponse
    {
        $activity = $this->service->complete($id);
        return $this->success(new CrmActivityResource($activity), 'Activity completed.');
    }
}
