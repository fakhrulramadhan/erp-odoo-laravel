<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\LeadResource;
use App\Services\CRM\LeadService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LeadService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'source', 'assigned_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($l) => (new LeadResource($l))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
            'source' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);
        $lead = $this->service->create($validated);
        return $this->created(new LeadResource($lead), 'Lead created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $lead = $this->service->find($id);
        return $this->success(new LeadResource($lead));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_id' => 'nullable|exists:companies,id',
            'source' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
        ]);
        $lead = $this->service->update($id, $validated);
        return $this->success(new LeadResource($lead), 'Lead updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Lead deleted successfully.');
    }

    public function qualify(int $id): JsonResponse
    {
        $lead = $this->service->qualify($id);
        return $this->success(new LeadResource($lead), 'Lead qualified successfully.');
    }

    public function markAsLost(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['lost_reason' => 'nullable|string']);
        $lead = $this->service->markAsLost($id, $validated);
        return $this->success(new LeadResource($lead), 'Lead marked as lost.');
    }
}
