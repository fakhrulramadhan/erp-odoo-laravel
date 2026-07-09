<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMoveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stock_picking_id' => $this->stock_picking_id,
            'move_number' => $this->move_number,
            'status' => $this->status?->value ?? $this->status,
            'origin' => $this->origin,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'lot_id' => $this->lot_id,
            'lot' => new LotResource($this->whenLoaded('lot')),
            'source_location_id' => $this->source_location_id,
            'source_location' => new StockLocationResource($this->whenLoaded('sourceLocation')),
            'destination_location_id' => $this->destination_location_id,
            'destination_location' => new StockLocationResource($this->whenLoaded('destinationLocation')),
            'quantity' => (float) $this->quantity,
            'reserved_qty' => (float) $this->reserved_qty,
            'done_qty' => (float) $this->done_qty,
            'unit_cost' => (float) $this->unit_cost,
            'total_cost' => (float) $this->total_cost,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'effective_date' => $this->effective_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
