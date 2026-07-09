<?php

namespace App\Http\Resources\CRM;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lead_number' => $this->lead_number,
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'source' => $this->source,
            'status' => $this->status,
            'score' => $this->score,
            'expected_revenue' => (float) $this->expected_revenue,
            'expected_close_date' => $this->expected_close_date?->toDateString(),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'description' => $this->description,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'opportunities' => OpportunityResource::collection($this->whenLoaded('opportunities')),
            'activities' => CrmActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
