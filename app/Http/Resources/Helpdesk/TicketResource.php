<?php

namespace App\Http\Resources\Helpdesk;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'customer_id' => $this->customer_id,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'category_id' => $this->category_id,
            'category' => TicketCategoryResource::make($this->whenLoaded('category')),
            'sla_id' => $this->sla_id,
            'sla' => SlaResource::make($this->whenLoaded('sla')),
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'assigned_user' => UserResource::make($this->whenLoaded('assignedUser')),
            'assigned_team_id' => $this->assigned_team_id,
            'first_response_at' => $this->first_response_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'is_escalated' => $this->is_escalated,
            'company_id' => $this->company_id,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'replies' => $this->whenLoaded('replies'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
