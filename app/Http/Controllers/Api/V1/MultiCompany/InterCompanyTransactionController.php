<?php

namespace App\Http\Controllers\Api\V1\MultiCompany;

use App\Http\Controllers\Controller;
use App\Services\MultiCompany\InterCompanyTransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterCompanyTransactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InterCompanyTransactionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'source_company_id' => 'required|exists:companies,id',
            'target_company_id' => 'required|exists:companies,id',
            'transaction_type' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        return $this->created($this->service->create($v), 'Inter-company transaction created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'amount' => 'sometimes|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        return $this->success($this->service->update($id, $v), 'Transaction updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Transaction deleted.'); }

    public function approve(int $id): JsonResponse
    {
        return $this->success($this->service->approve($id), 'Transaction approved.');
    }

    public function reject(int $id): JsonResponse
    {
        return $this->success($this->service->reject($id), 'Transaction rejected.');
    }
}
