<?php

namespace App\Http\Resources\Helpdesk;

use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SlaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'response_time_hours' => $this->response_time_hours,
            'resolution_time_hours' => $this->resolution_time_hours,
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
