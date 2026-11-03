<?php

namespace App\Http\Controllers\Api\V1\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ecommerce\EcommerceProductResource;
use App\Services\Ecommerce\EcommerceProductService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcommerceProductController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EcommerceProductService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'category_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($p) => (new EcommerceProductResource($p))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'slug' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_featured' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'sequence' => 'nullable|integer|min:0',
            'company_id' => 'nullable|exists:companies,id',
            'images' => 'nullable|array',
            'images.*.image_path' => 'required|string|max:500',
            'images.*.alt_text' => 'nullable|string|max:255',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required|string|max:255',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
        ]);
        $product = $this->service->create($validated);
        return $this->created(new EcommerceProductResource($product), 'E-commerce product created.');
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->service->find($id);
        return $this->success(new EcommerceProductResource($product));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'slug' => 'nullable|string|max:255',
            'short_description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_featured' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'sequence' => 'nullable|integer|min:0',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $product = $this->service->update($id, $validated);
        return $this->success(new EcommerceProductResource($product), 'E-commerce product updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('E-commerce product deleted.');
    }
}
