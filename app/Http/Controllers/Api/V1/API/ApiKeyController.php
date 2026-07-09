<?php

namespace App\Http\Controllers\Api\V1\API;

use App\Http\Controllers\Controller;
use App\Services\API\ApiKeyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ApiKeyService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'string|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);
        return $this->created($this->service->create($v), 'API key created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'string|max:100',
        ]);
        return $this->success($this->service->update($id, $v), 'API key updated.');
    }

    public function revoke(int $id): JsonResponse
    {
        return $this->success($this->service->revoke($id), 'API key revoked.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('API key deleted.'); }
}
