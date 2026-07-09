<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'equipment_id' => $this->equipment_id,
            'equipment' => new EquipmentResource($this->whenLoaded('equipment')),
            'maintenance_type' => $this->maintenance_type,
            'priority' => $this->priority,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'description' => $this->description,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'actual_start' => $this->actual_start?->toIso8601String(),
            'actual_finish' => $this->actual_finish?->toIso8601String(),
            'estimated_duration' => $this->estimated_duration,
            'actual_duration' => $this->actual_duration,
            'cost' => (float) ($this->cost ?? 0),
            'assigned_to' => $this->assigned_to,
            'assignee' => new UserResource($this->whenLoaded('assignedTo')),
            'checklist' => $this->checklist,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
