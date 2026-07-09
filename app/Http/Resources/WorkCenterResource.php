<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkCenterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'capacity_per_hour' => (float) ($this->capacity_per_hour ?? 0),
            'cost_per_hour' => (float) ($this->cost_per_hour ?? 0),
            'efficiency' => (float) ($this->efficiency ?? 100),
            'is_active' => (bool) $this->is_active,
            'operators' => UserResource::collection($this->whenLoaded('operators')),
            'equipment' => EquipmentResource::collection($this->whenLoaded('equipment')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
