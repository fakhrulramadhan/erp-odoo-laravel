<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\PayrollComponentResource;
use App\Services\Payroll\PayrollComponentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollComponentController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PayrollComponentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'type']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($pc) => (new PayrollComponentResource($pc))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'type' => 'required|string|max:50',
            'calculation_type' => 'nullable|string|max:50',
            'default_amount' => 'nullable|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        $pc = $this->service->create($validated);
        return $this->created(new PayrollComponentResource($pc), 'Payroll component created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $pc = $this->service->find($id);
        return $this->success(new PayrollComponentResource($pc));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:50',
            'calculation_type' => 'nullable|string|max:50',
            'default_amount' => 'nullable|numeric|min:0',
            'is_taxable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);
        $pc = $this->service->update($id, $validated);
        return $this->success(new PayrollComponentResource($pc), 'Payroll component updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Payroll component deleted successfully.');
    }
}
