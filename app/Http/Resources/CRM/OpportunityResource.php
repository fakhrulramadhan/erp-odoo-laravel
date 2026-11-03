<?php

namespace App\Http\Resources\CRM;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'opportunity_number' => $this->opportunity_number,
            'name' => $this->name,
            'lead_id' => $this->lead_id,
            'lead' => LeadResource::make($this->whenLoaded('lead')),
            'customer_id' => $this->customer_id,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'stage' => $this->stage,
            'probability' => $this->probability,
            'expected_revenue' => (float) $this->expected_revenue,
            'actual_revenue' => (float) $this->actual_revenue,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'actual_close_date' => $this->actual_close_date?->toDateString(),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => UserResource::make($this->whenLoaded('assignedUser')),
            'description' => $this->description,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'activities' => CrmActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
