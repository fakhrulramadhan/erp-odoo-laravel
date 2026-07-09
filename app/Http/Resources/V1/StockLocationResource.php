<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockLocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse'    => new WarehouseResource($this->whenLoaded('warehouse')),
            'code'         => $this->code,
            'name'         => $this->name,
            'type'         => $this->type,
            'parent_id'    => $this->parent_id,
            'parent'       => new self($this->whenLoaded('parent')),
            'description'  => $this->description,
            'is_active'    => $this->is_active,
            'creator'      => new UserResource($this->whenLoaded('creator')),
            'created_at'   => $this->created_at?->toISOString(),
            'updated_at'   => $this->updated_at?->toISOString(),
        ];
    }
}
