<?php

namespace App\Http\Controllers\Api\V1\HRM;

use App\Http\Controllers\Controller;
use App\Http\Resources\HRM\EmployeeResource;
use App\Services\HRM\EmployeeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EmployeeService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'department_id', 'position_id', 'branch_id', 'employment_type']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($e) => (new EmployeeResource($e))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:50',
            'company_id' => 'required|exists:companies,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employment_type' => 'nullable|string',
            'hire_date' => 'required|date',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);
        $employee = $this->service->create($validated);
        return $this->created(new EmployeeResource($employee), 'Employee created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $employee = $this->service->find($id);
        return $this->success(new EmployeeResource($employee));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => "sometimes|required|email|max:255|unique:employees,email,{$id}",
            'phone' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employment_type' => 'nullable|string',
            'hire_date' => 'sometimes|required|date',
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $employee = $this->service->update($id, $validated);
        return $this->success(new EmployeeResource($employee), 'Employee updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Employee deleted successfully.');
    }

    public function terminate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'termination_date' => 'required|date',
            'termination_reason' => 'nullable|string',
        ]);
        $employee = $this->service->terminate($id, $validated);
        return $this->success(new EmployeeResource($employee), 'Employee terminated.');
    }
}
