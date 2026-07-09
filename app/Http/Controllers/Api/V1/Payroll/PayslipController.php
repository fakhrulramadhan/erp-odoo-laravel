<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\PayslipResource;
use App\Services\Payroll\PayslipService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PayslipService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'payroll_id', 'employee_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($ps) => (new PayslipResource($ps))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
            'employee_id' => 'required|exists:employees,id',
            'salary_structure_id' => 'nullable|exists:salary_structures,id',
            'lines' => 'nullable|array',
            'lines.*.component_id' => 'required|exists:payroll_components,id',
            'lines.*.amount' => 'required|numeric|min:0',
            'lines.*.type' => 'required|in:earning,deduction',
        ]);
        $ps = $this->service->create($validated);
        return $this->created(new PayslipResource($ps), 'Payslip created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $ps = $this->service->find($id);
        return $this->success(new PayslipResource($ps));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Payslip deleted successfully.');
    }

    public function confirm(int $id): JsonResponse
    {
        $ps = $this->service->confirm($id);
        return $this->success(new PayslipResource($ps), 'Payslip confirmed.');
    }

    public function pay(int $id): JsonResponse
    {
        $ps = $this->service->pay($id);
        return $this->success(new PayslipResource($ps), 'Payslip marked as paid.');
    }
}
