<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequisitionLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_requisition_id' => $this->purchase_requisition_id,
            'line_number' => $this->line_number,
            'description' => $this->description,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'quantity' => (float) $this->quantity,
            'estimated_price' => (float) $this->estimated_price,
            'required_date' => $this->required_date?->toDateString(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
