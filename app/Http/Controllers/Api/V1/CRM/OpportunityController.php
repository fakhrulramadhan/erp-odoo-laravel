<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\CRM\OpportunityResource;
use App\Services\CRM\OpportunityService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected OpportunityService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'stage', 'customer_id', 'assigned_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($o) => (new OpportunityResource($o))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);
        $opp = $this->service->create($validated);
        return $this->created(new OpportunityResource($opp), 'Opportunity created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $opp = $this->service->find($id);
        return $this->success(new OpportunityResource($opp));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'description' => 'nullable|string',
        ]);
        $opp = $this->service->update($id, $validated);
        return $this->success(new OpportunityResource($opp), 'Opportunity updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Opportunity deleted successfully.');
    }

    public function updateStage(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['stage' => 'required|string']);
        $opp = $this->service->updateStage($id, $validated['stage']);
        return $this->success(new OpportunityResource($opp), 'Opportunity stage updated.');
    }
}
