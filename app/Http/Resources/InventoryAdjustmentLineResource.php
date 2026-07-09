<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryAdjustmentLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'inventory_adjustment_id' => $this->inventory_adjustment_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'location_id' => $this->location_id,
            'location' => new StockLocationResource($this->whenLoaded('location')),
            'lot_id' => $this->lot_id,
            'lot' => new LotResource($this->whenLoaded('lot')),
            'theoretical_qty' => (float) $this->theoretical_qty,
            'actual_qty' => (float) $this->actual_qty,
            'difference' => (float) $this->difference,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
