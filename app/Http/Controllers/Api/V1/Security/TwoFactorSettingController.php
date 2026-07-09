<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\TwoFactorSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorSettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TwoFactorSettingService $service
    ) {}

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_enabled' => 'nullable|boolean',
            'method' => 'nullable|string|max:20',
        ]);
        return $this->success($this->service->createOrUpdate($v), '2FA settings saved.');
    }

    public function enable(Request $request): JsonResponse
    {
        $v = $request->validate(['user_id' => 'required|exists:users,id']);
        return $this->success($this->service->enable($v['user_id']), '2FA enabled.');
    }

    public function disable(Request $request): JsonResponse
    {
        $v = $request->validate(['user_id' => 'required|exists:users,id']);
        return $this->success($this->service->disable($v['user_id']), '2FA disabled.');
    }
}
