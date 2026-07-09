<?php

namespace App\Http\Resources\CRM;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrmActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activity_type' => $this->activity_type,
            'subject' => $this->subject,
            'description' => $this->description,
            'activityable_type' => $this->activityable_type,
            'activityable_id' => $this->activityable_id,
            'due_date' => $this->due_date?->toIso8601String(),
            'assigned_to' => $this->assigned_to,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'is_done' => $this->is_done,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'result' => $this->result,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
