<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\PayrollResource;
use App\Services\Payroll\PayrollService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PayrollService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'period_id', 'company_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($p) => (new PayrollResource($p))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:payroll_periods,id',
            'company_id' => 'required|exists:companies,id',
            'name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $payroll = $this->service->create($validated);
        return $this->created(new PayrollResource($payroll), 'Payroll created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $payroll = $this->service->find($id);
        return $this->success(new PayrollResource($payroll));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $payroll = $this->service->update($id, $validated);
        return $this->success(new PayrollResource($payroll), 'Payroll updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Payroll deleted successfully.');
    }

    public function process(int $id): JsonResponse
    {
        $payroll = $this->service->process($id);
        return $this->success(new PayrollResource($payroll), 'Payroll processed.');
    }

    public function confirm(int $id): JsonResponse
    {
        $payroll = $this->service->confirm($id);
        return $this->success(new PayrollResource($payroll), 'Payroll confirmed.');
    }

    public function approve(int $id): JsonResponse
    {
        $payroll = $this->service->approve($id);
        return $this->success(new PayrollResource($payroll), 'Payroll approved.');
    }

    public function cancel(int $id): JsonResponse
    {
        $payroll = $this->service->cancel($id);
        return $this->success(new PayrollResource($payroll), 'Payroll cancelled.');
    }
}
