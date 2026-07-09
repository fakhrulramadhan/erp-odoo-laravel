<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scrap_number' => $this->scrap_number,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'location_id' => $this->location_id,
            'location' => new StockLocationResource($this->whenLoaded('location')),
            'quantity' => (float) $this->quantity,
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'scrap_type' => $this->scrap_type,
            'reason' => $this->reason,
            'unit_cost' => (float) ($this->unit_cost ?? 0),
            'total_cost' => (float) ($this->total_cost ?? 0),
            'status' => $this->status,
            'scrap_date' => $this->scrap_date?->toDateString(),
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
