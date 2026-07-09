<?php

namespace App\Http\Controllers\Api\V1\Manufacturing;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScrapOrderResource;
use App\Services\Manufacturing\ScrapOrderService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScrapOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ScrapOrderService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'scrap_type', 'product_id', 'date_from', 'date_to']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($s) => (new ScrapOrderResource($s))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|numeric|min:0.0001',
            'uom_id' => 'nullable|exists:unit_of_measures,id',
            'scrap_type' => 'required|string|max:50',
            'reason' => 'required|string',
            'scrap_date' => 'nullable|date',
            'source_type' => 'nullable|string',
            'source_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);
        $scrap = $this->service->create($validated);
        return $this->created(new ScrapOrderResource($scrap), 'Scrap order created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $scrap = $this->service->find($id);
        return $this->success(new ScrapOrderResource($scrap));
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Scrap order deleted successfully.');
    }

    public function confirm(int $id): JsonResponse
    {
        $scrap = $this->service->confirm($id);
        return $this->success(new ScrapOrderResource($scrap), 'Scrap order confirmed.');
    }

    public function process(int $id): JsonResponse
    {
        $scrap = $this->service->process($id);
        return $this->success(new ScrapOrderResource($scrap), 'Scrap order processed.');
    }

    public function cancel(int $id): JsonResponse
    {
        $scrap = $this->service->cancel($id);
        return $this->success(new ScrapOrderResource($scrap), 'Scrap order cancelled.');
    }
}
