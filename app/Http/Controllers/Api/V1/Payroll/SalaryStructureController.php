<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\SalaryStructureResource;
use App\Services\Payroll\SalaryStructureService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SalaryStructureService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($ss) => (new SalaryStructureResource($ss))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.component_id' => 'required|exists:payroll_components,id',
            'lines.*.amount' => 'nullable|numeric|min:0',
            'lines.*.percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.base_component_id' => 'nullable|exists:payroll_components,id',
        ]);
        $ss = $this->service->create($validated);
        return $this->created(new SalaryStructureResource($ss), 'Salary structure created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $ss = $this->service->find($id);
        return $this->success(new SalaryStructureResource($ss));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.component_id' => 'required|exists:payroll_components,id',
            'lines.*.amount' => 'nullable|numeric|min:0',
            'lines.*.percentage' => 'nullable|numeric|min:0|max:100',
            'lines.*.base_component_id' => 'nullable|exists:payroll_components,id',
        ]);
        $ss = $this->service->update($id, $validated);
        return $this->success(new SalaryStructureResource($ss), 'Salary structure updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Salary structure deleted successfully.');
    }
}
