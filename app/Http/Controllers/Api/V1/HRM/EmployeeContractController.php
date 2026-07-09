<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\HRM\EmployeeContractResource;
use App\Services\HRM\EmployeeContractService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeContractController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EmployeeContractService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'employee_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($c) => (new EmployeeContractResource($c))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'contract_number' => 'nullable|string|max:50',
            'contract_type' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'salary' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'notes' => 'nullable|string',
        ]);
        $contract = $this->service->create($validated);
        return $this->created(new EmployeeContractResource($contract), 'Contract created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $contract = $this->service->find($id);
        return $this->success(new EmployeeContractResource($contract));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'contract_type' => 'sometimes|required|string|max:50',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $contract = $this->service->update($id, $validated);
        return $this->success(new EmployeeContractResource($contract), 'Contract updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Contract deleted successfully.');
    }
}
