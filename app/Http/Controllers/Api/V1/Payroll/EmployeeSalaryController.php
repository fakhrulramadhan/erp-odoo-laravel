<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\EmployeeSalaryResource;
use App\Services\Payroll\EmployeeSalaryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EmployeeSalaryService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'employee_id', 'structure_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($es) => (new EmployeeSalaryResource($es))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'structure_id' => 'required|exists:salary_structures,id',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
            'base_salary' => 'nullable|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'notes' => 'nullable|string',
        ]);
        $es = $this->service->create($validated);
        return $this->created(new EmployeeSalaryResource($es), 'Employee salary created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $es = $this->service->find($id);
        return $this->success(new EmployeeSalaryResource($es));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'structure_id' => 'sometimes|required|exists:salary_structures,id',
            'effective_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date',
            'base_salary' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $es = $this->service->update($id, $validated);
        return $this->success(new EmployeeSalaryResource($es), 'Employee salary updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Employee salary deleted successfully.');
    }
}
