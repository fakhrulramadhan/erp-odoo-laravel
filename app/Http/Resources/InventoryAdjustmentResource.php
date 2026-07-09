<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAdjustmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'adjustment_number' => $this->adjustment_number,
            'status' => $this->status,
            'adjustment_date' => $this->adjustment_date?->toDateString(),
            'reason' => $this->reason,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'validated_by' => $this->validated_by,
            'validator' => new UserResource($this->whenLoaded('validator')),
            'validated_at' => $this->validated_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'lines' => InventoryAdjustmentLineResource::collection($this->whenLoaded('lines')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
