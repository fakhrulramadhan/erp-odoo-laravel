<?php

namespace App\Http\Controllers\Api\V1\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\Ecommerce\PromoCodeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PromoCodeService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'description' => 'nullable|string',
            'discount_type' => 'required|string|max:20',
            'discount_value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);
        return $this->created($this->service->create($v), 'Promo code created.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function update(Request $request, int $id): JsonResponse
    {
        $v = $request->validate([
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|string|max:20',
            'discount_value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);
        return $this->success($this->service->update($id, $v), 'Promo code updated.');
    }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Promo code deleted.'); }

    public function validate(Request $request): JsonResponse
    {
        $v = $request->validate([
            'code' => 'required|string|max:50',
            'order_amount' => 'required|numeric|min:0',
        ]);
        $promo = $this->service->validate($v['code'], $v['order_amount']);
        if (!$promo) return $this->error('Invalid or expired promo code.', 422);
        return $this->success($promo, 'Promo code is valid.');
    }
}
