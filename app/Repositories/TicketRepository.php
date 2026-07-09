<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class TicketRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Ticket());
    }

    public function getWithRelations(array $filters = [], ?int $perPage = 15)
    {
        $query = Ticket::with(['customer', 'category', 'sla', 'assignedUser', 'company', 'branch', 'creator']);
        $this->applyFilters($query, $filters);
        return $query->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Ticket
    {
        return Ticket::with([
            'customer', 'category', 'sla', 'assignedUser',
            'company', 'branch',
            'replies.user', 'replies.customer', 'creator',
        ])->findOrFail($id);
    }

    public function getNextTicketNumber(): string
    {
        $last = Ticket::withTrashed()->orderBy('id', 'desc')->first();
        $next = $last ? (int) substr($last->ticket_number, 3) + 1 : 1;
        return 'TK-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        parent::applyFilters($query, $filters);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $search = $filters['search'];
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
    }
}
