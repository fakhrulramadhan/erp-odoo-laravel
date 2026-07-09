<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierQuotationLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_quotation_id' => $this->supplier_quotation_id,
            'line_number' => $this->line_number,
            'description' => $this->description,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'quantity' => (float) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'discount_rate' => (float) $this->discount_rate,
            'discount_amount' => (float) $this->discount_amount,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'vendor_price' => (float) $this->vendor_price,
            'vendor_lead_days' => $this->vendor_lead_days,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
