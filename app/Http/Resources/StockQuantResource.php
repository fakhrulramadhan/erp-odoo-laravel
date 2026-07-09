<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockQuantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'location_id' => $this->location_id,
            'location' => new StockLocationResource($this->whenLoaded('location')),
            'lot_id' => $this->lot_id,
            'lot' => new LotResource($this->whenLoaded('lot')),
            'quantity' => (float) $this->quantity,
            'reserved_quantity' => (float) $this->reserved_quantity,
            'available_quantity' => (float) $this->available_quantity,
            'incoming_quantity' => (float) $this->incoming_quantity,
            'outgoing_quantity' => (float) $this->outgoing_quantity,
            'unit_cost' => (float) $this->unit_cost,
            'total_value' => (float) $this->total_value,
            'last_updated_at' => $this->last_updated_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
