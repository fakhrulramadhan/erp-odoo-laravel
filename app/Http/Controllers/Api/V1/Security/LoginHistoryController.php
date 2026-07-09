<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\LoginHistoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LoginHistoryService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }
}
