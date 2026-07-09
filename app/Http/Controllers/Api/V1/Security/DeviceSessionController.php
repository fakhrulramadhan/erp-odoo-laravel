<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\DeviceSessionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceSessionController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected DeviceSessionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function revoke(int $id): JsonResponse
    {
        $this->service->revoke($id);
        return $this->success(null, 'Session revoked.');
    }

    public function revokeAll(Request $request): JsonResponse
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $count = $this->service->revokeAllForUser($validated['user_id']);
        return $this->success(['revoked' => $count], "Revoked {$count} sessions.");
    }
}
