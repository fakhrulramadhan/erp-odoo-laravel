<?php

namespace App\Http\Controllers\Api\V1\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\Ecommerce\ShippingMethodService;
use App\Services\Ecommerce\PromoCodeService;
use App\Services\Ecommerce\ProductReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ShippingMethodService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'base_cost' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);
        return $this->created($this->service->create($v), 'Shipping method created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'base_cost' => 'nullable|numeric|min:0',
            'estimated_days' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);
        return $this->success($this->service->update($id, $v), 'Shipping method updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Shipping method deleted.'); }
}
