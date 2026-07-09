<?php

namespace App\Http\Controllers\Api\V1\API;

use App\Http\Controllers\Controller;
use App\Services\API\WebhookService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WebhookService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string|max:100',
            'description' => 'nullable|string',
        ]);
        return $this->created($this->service->create($v), 'Webhook created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'url' => 'sometimes|required|url|max:500',
            'events' => 'nullable|array|min:1',
            'events.*' => 'string|max:100',
            'description' => 'nullable|string',
        ]);
        return $this->success($this->service->update($id, $v), 'Webhook updated.');
    }

    public function toggle(int $id): JsonResponse
    {
        return $this->success($this->service->toggle($id), 'Webhook toggled.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Webhook deleted.'); }
}
