<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationPreferenceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected NotificationPreferenceService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'user_id' => 'required|exists:users,id',
            'email_enabled' => 'nullable|boolean',
            'push_enabled' => 'nullable|boolean',
            'sms_enabled' => 'nullable|boolean',
        ]);
        return $this->success($this->service->createOrUpdate($v), 'Preferences saved.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Preference deleted.'); }
}
