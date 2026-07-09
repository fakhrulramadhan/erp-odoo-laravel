<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'manufacturing_order_id' => $this->manufacturing_order_id,
            'routing_operation_id' => $this->routing_operation_id,
            'name' => $this->name,
            'work_center_id' => $this->work_center_id,
            'work_center' => new WorkCenterResource($this->whenLoaded('workCenter')),
            'sequence' => $this->sequence,
            'status' => $this->status,
            'planned_duration_minutes' => (float) ($this->planned_duration_minutes ?? 0),
            'actual_duration_minutes' => (float) ($this->actual_duration_minutes ?? 0),
            'cost' => (float) ($this->cost ?? 0),
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'assigned_to' => $this->assigned_to,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
