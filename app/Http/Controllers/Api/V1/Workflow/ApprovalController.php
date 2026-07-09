<?php

namespace App\Http\Controllers\Api\V1\Workflow;

use App\Http\Controllers\Controller;
use App\Services\Workflow\ApprovalService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ApprovalService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'requested_by']);
        $perPage = $request->input('per_page', 15);
        return $this->paginated($this->service->list($filters, $perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'workflow_step_id' => 'nullable|exists:workflow_steps,id',
            'requestable_type' => 'nullable|string|max:100',
            'requestable_id' => 'nullable|integer',
            'comment' => 'nullable|string',
        ]);
        return $this->created($this->service->create($v), 'Approval request created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function approve(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['comment' => 'nullable|string']);
        return $this->success($this->service->approve($id, $v['comment'] ?? null), 'Approval granted.');
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $v = $request->validate(['comment' => 'nullable|string']);
        return $this->success($this->service->reject($id, $v['comment'] ?? ''), 'Approval rejected.');
    }
}
