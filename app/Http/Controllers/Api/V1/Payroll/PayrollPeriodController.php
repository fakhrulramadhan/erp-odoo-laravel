<?php

namespace App\Http\Controllers\Api\V1\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\PayrollPeriodResource;
use App\Services\Payroll\PayrollPeriodService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PayrollPeriodService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'year']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($pp) => (new PayrollPeriodResource($pp))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'company_id' => 'required|exists:companies,id',
        ]);
        $pp = $this->service->create($validated);
        return $this->created(new PayrollPeriodResource($pp), 'Payroll period created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $pp = $this->service->find($id);
        return $this->success(new PayrollPeriodResource($pp));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
        ]);
        $pp = $this->service->update($id, $validated);
        return $this->success(new PayrollPeriodResource($pp), 'Payroll period updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Payroll period deleted successfully.');
    }
}
