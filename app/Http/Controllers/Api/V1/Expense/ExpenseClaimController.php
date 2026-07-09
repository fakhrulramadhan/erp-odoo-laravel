<?php

namespace App\Http\Controllers\Api\V1\Expense;

use App\Http\Controllers\Controller;
use App\Http\Resources\Expense\ExpenseClaimResource;
use App\Services\Expense\ExpenseClaimService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseClaimController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ExpenseClaimService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'employee_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($c) => (new ExpenseClaimResource($c))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'company_id' => 'required|exists:companies,id',
            'currency_id' => 'required|exists:currencies,id',
            'claim_date' => 'required|date',
            'description' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.category' => 'required|string|max:100',
            'lines.*.description' => 'nullable|string',
            'lines.*.amount' => 'required|numeric|min:0',
            'lines.*.date' => 'required|date',
            'lines.*.receipt_path' => 'nullable|string|max:500',
        ]);
        $claim = $this->service->create($validated);
        return $this->created(new ExpenseClaimResource($claim), 'Expense claim created.');
    }

    public function show(int $id): JsonResponse
    {
        $claim = $this->service->find($id);
        return $this->success(new ExpenseClaimResource($claim));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'lines' => 'sometimes|array|min:1',
            'lines.*.category' => 'required|string|max:100',
            'lines.*.description' => 'nullable|string',
            'lines.*.amount' => 'required|numeric|min:0',
            'lines.*.date' => 'required|date',
            'lines.*.receipt_path' => 'nullable|string|max:500',
        ]);
        $claim = $this->service->update($id, $validated);
        return $this->success(new ExpenseClaimResource($claim), 'Expense claim updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Expense claim deleted.');
    }

    public function submit(int $id): JsonResponse
    {
        $claim = $this->service->submit($id);
        return $this->success(new ExpenseClaimResource($claim), 'Expense claim submitted.');
    }

    public function approve(int $id): JsonResponse
    {
        $claim = $this->service->approve($id);
        return $this->success(new ExpenseClaimResource($claim), 'Expense claim approved.');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['reason' => 'nullable|string']);
        $claim = $this->service->reject($id, $validated['reason'] ?? '');
        return $this->success(new ExpenseClaimResource($claim), 'Expense claim rejected.');
    }

    public function markPaid(int $id): JsonResponse
    {
        $claim = $this->service->markPaid($id);
        return $this->success(new ExpenseClaimResource($claim), 'Expense claim marked as paid.');
    }
}
