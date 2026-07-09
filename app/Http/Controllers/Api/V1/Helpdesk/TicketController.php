<?php

namespace App\Http\Controllers\Api\V1\Helpdesk;

use App\Http\Controllers\Controller;
use App\Http\Resources\Helpdesk\TicketResource;
use App\Services\Helpdesk\TicketService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TicketService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'priority', 'assigned_to', 'customer_id', 'category_id']);
        $perPage = $request->input('per_page', 15);
        $paginator = $this->service->list($filters, $perPage);
        $paginator->getCollection()->transform(fn($t) => (new TicketResource($t))->resolve($request));
        return $this->paginated($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'customer_id' => 'nullable|exists:customers,id',
            'category_id' => 'nullable|exists:ticket_categories,id',
            'priority' => 'nullable|string|max:20',
            'sla_id' => 'nullable|exists:slas,id',
            'company_id' => 'nullable|exists:companies,id',
        ]);
        $ticket = $this->service->create($validated);
        return $this->created(new TicketResource($ticket), 'Ticket created successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $ticket = $this->service->find($id);
        return $this->success(new TicketResource($ticket));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'nullable|exists:ticket_categories,id',
            'priority' => 'nullable|string|max:20',
            'sla_id' => 'nullable|exists:slas,id',
        ]);
        $ticket = $this->service->update($id, $validated);
        return $this->success(new TicketResource($ticket), 'Ticket updated successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return $this->deleted('Ticket deleted successfully.');
    }

    public function assign(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $ticket = $this->service->assign($id, $validated['user_id']);
        return $this->success(new TicketResource($ticket), 'Ticket assigned.');
    }

    public function resolve(int $id): JsonResponse
    {
        $ticket = $this->service->resolve($id);
        return $this->success(new TicketResource($ticket), 'Ticket resolved.');
    }

    public function close(int $id): JsonResponse
    {
        $ticket = $this->service->close($id);
        return $this->success(new TicketResource($ticket), 'Ticket closed.');
    }

    public function addReply(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);
        $ticket = $this->service->addReply($id, $validated);
        return $this->success(new TicketResource($ticket), 'Reply added.');
    }
}
