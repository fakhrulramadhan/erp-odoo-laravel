<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BomLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bom_id' => $this->bom_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'quantity' => (float) $this->quantity,
            'scrap_rate' => (float) ($this->scrap_rate ?? 0),
            'description' => $this->description,
            'sequence' => $this->sequence,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
