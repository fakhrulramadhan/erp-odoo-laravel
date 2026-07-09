<?php

namespace App\Http\Controllers\Api\V1\Ecommerce;

use App\Http\Controllers\Controller;
use App\Services\Ecommerce\EcommerceSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EcommerceSettingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EcommerceSettingService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['company_id']);
        $perPage = $request->input('per_page', 15);
        return $this->paginated($this->service->list($filters, $perPage));
    }

    public function show(int $id): JsonResponse
    {
        return $this->success($this->service->find($id));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'store_name' => 'nullable|string|max:255',
            'store_description' => 'nullable|string',
            'currency_id' => 'nullable|exists:currencies,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'enable_reviews' => 'nullable|boolean',
            'enable_wishlist' => 'nullable|boolean',
            'items_per_page' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);
        $setting = $this->service->createOrUpdate($validated);
        return $this->success($setting, 'E-commerce settings saved.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'store_description' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'enable_reviews' => 'nullable|boolean',
            'enable_wishlist' => 'nullable|boolean',
            'items_per_page' => 'nullable|integer|min:1',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
        ]);
        $setting = $this->service->update($id, $validated);
        return $this->success($setting, 'E-commerce settings updated.');
    }
}
