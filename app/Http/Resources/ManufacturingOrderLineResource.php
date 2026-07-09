<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturingOrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'manufacturing_order_id' => $this->manufacturing_order_id,
            'line_number' => $this->line_number,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'quantity' => (float) $this->quantity,
            'consumed_qty' => (float) ($this->consumed_qty ?? 0),
            'reserved_qty' => (float) ($this->reserved_qty ?? 0),
            'scrap_qty' => (float) ($this->scrap_qty ?? 0),
            'line_type' => $this->line_type,
            'unit_cost' => (float) ($this->unit_cost ?? 0),
            'total_cost' => (float) ($this->total_cost ?? 0),
            'source_location_id' => $this->source_location_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
