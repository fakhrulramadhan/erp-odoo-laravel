<?php

namespace App\Services\Helpdesk;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Repositories\TicketRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TicketService
{
    public function __construct(
        protected TicketRepository $repository
    ) {}

    public function list(array $filters = [], ?int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getWithRelations($filters, $perPage);
    }

    public function find(int $id): Ticket
    {
        return $this->repository->findWithDetails($id);
    }

    public function create(array $data): Ticket
    {
        return DB::transaction(function () use ($data) {
            $data['ticket_number'] = $this->repository->getNextTicketNumber();
            $data['status'] = TicketStatus::Open;
            $data['created_by'] = auth()->id();
            return $this->repository->create($data);
        });
    }

    public function update(int $id, array $data): Ticket
    {
        return DB::transaction(function () use ($id, $data) {
            $data['updated_by'] = auth()->id();
            return $this->repository->update($id, $data);
        });
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function assign(int $id, int $userId): Ticket
    {
        return DB::transaction(function () use ($id, $userId) {
            return $this->repository->update($id, [
                'assigned_to' => $userId,
                'status' => TicketStatus::InProgress,
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function resolve(int $id): Ticket
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => TicketStatus::Resolved,
                'resolved_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function close(int $id): Ticket
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->update($id, [
                'status' => TicketStatus::Closed,
                'closed_at' => now(),
                'updated_by' => auth()->id(),
            ]);
        });
    }

    public function addReply(int $id, array $data): Ticket
    {
        return DB::transaction(function () use ($id, $data) {
            $ticket = $this->repository->findById($id);
            $data['user_id'] = auth()->id();
            $data['created_by'] = auth()->id();
            $ticket->replies()->create($data);

            if (empty($ticket->first_response_at)) {
                $ticket->update(['first_response_at' => now()]);
            }

            return $this->repository->findWithDetails($id);
        });
    }
}
