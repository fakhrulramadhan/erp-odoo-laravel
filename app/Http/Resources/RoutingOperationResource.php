<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoutingOperationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'routing_id' => $this->routing_id,
            'sequence' => $this->sequence,
            'name' => $this->name,
            'work_center_id' => $this->work_center_id,
            'work_center' => new WorkCenterResource($this->whenLoaded('workCenter')),
            'duration_minutes' => (float) ($this->duration_minutes ?? 0),
            'setup_time_minutes' => (float) ($this->setup_time_minutes ?? 0),
            'cost_per_hour' => (float) ($this->cost_per_hour ?? 0),
            'description' => $this->description,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
