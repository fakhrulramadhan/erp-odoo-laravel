<?php

namespace App\Http\Controllers\Api\V1\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\Ecommerce\ProductReviewService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProductReviewService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all(), $request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $v = $request->validate([
            'ecommerce_product_id' => 'required|exists:ecommerce_products,id',
            'customer_id' => 'nullable|exists:customers,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
        ]);
        return $this->created($this->service->create($v), 'Review submitted.');
    }

    public function show(int $id): JsonResponse { return $this->success($this->service->find($id)); }

    public function destroy(int $id): JsonResponse { $this->service->delete($id); return $this->deleted('Review deleted.'); }

    public function approve(int $id): JsonResponse
    {
        return $this->success($this->service->approve($id), 'Review approved.');
    }
}
