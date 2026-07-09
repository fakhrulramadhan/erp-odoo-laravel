<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationTemplateService;
use App\Services\Notification\NotificationPreferenceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NotificationTemplateService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:notification_templates,code',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'channel' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);
        return $this->created($this->service->create($v), 'Template created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'sometimes|required|string',
            'channel' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);
        return $this->success($this->service->update($id, $v), 'Template updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Template deleted.'); }
}
