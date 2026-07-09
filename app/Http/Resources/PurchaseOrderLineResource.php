<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'line_number' => $this->line_number,
            'description' => $this->description,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'tax_id' => $this->tax_id,
            'tax' => new TaxSettingResource($this->whenLoaded('tax')),
            'quantity' => (float) $this->quantity,
            'received_qty' => (float) $this->received_qty,
            'unit_price' => (float) ($this->price ?? 0),
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) ($this->tax_amount ?? 0),
            'discount_rate' => (float) ($this->discount_percent ?? 0),
            'discount_amount' => (float) $this->discount_amount,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'delivery_date' => $this->delivery_date?->toDateString(),
            'analytic_account' => $this->analytic_account,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
