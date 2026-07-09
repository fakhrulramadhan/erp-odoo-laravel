<?php

namespace App\Http\Controllers\Api\V1\POS;

use App\Http\Controllers\Controller;
use App\Http\Resources\POS\PosSessionResource;
use App\Services\POS\PosSessionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosSessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PosSessionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'user_id', 'pos_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($s) => (new PosSessionResource($s))->resolve($request));
        return $this->paginated($paginator);
    }

    public function show(int $id): JsonResponse
    {
        $session = $this->service->find($id);
        return $this->success(new PosSessionResource($session));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('POS session deleted successfully.');
    }

    public function open(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pos_id' => 'required|string|max:50',
            'opening_balance' => 'required|numeric|min:0',
            'currency_id' => 'nullable|exists:currencies,id',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        $session = $this->service->open($validated);
        return $this->created(new PosSessionResource($session), 'POS session opened.');
    }

    public function close(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        $session = $this->service->close($id, $validated);
        return $this->success(new PosSessionResource($session), 'POS session closed.');
    }
}
