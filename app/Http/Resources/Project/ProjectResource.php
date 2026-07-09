<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_number' => $this->project_number,
            'name' => $this->name,
            'description' => $this->description,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'manager_id' => $this->manager_id,
            'manager' => $this->whenLoaded('manager', fn() => [
                'id' => $this->manager->id,
                'full_name' => $this->manager->full_name,
            ]),
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'actual_end_date' => $this->actual_end_date?->toDateString(),
            'budget' => (float) $this->budget,
            'actual_cost' => (float) $this->actual_cost,
            'billing_rate' => (float) $this->billing_rate,
            'billing_type' => $this->billing_type,
            'progress' => $this->progress,
            'members' => $this->whenLoaded('members'),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'milestones' => MilestoneResource::collection($this->whenLoaded('milestones')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
