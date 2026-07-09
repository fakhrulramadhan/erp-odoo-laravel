<?php

namespace App\Http\Controllers\Api\V1\Helpdesk;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helpdesk\TicketCategoryResource;
use App\Services\Helpdesk\TicketCategoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TicketCategoryService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($tc) => (new TicketCategoryResource($tc))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $tc = $this->service->create($validated);
        return $this->created(new TicketCategoryResource($tc), 'Ticket category created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $tc = $this->service->find($id);
        return $this->success(new TicketCategoryResource($tc));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);
        $tc = $this->service->update($id, $validated);
        return $this->success(new TicketCategoryResource($tc), 'Ticket category updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Ticket category deleted successfully.');
    }
}
