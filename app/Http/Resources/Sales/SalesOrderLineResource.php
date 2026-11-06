<?php

namespace App\Http\Resources\Sales;

use App\Http\Resources\{CustomerResource, CurrencyResource, WarehouseResource, UserResource};
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'line_number' => $this->line_number,
            'product_id' => $this->product_id,
            'product' => \App\Http\Resources\ProductResource::make($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => \App\Http\Resources\UnitOfMeasureResource::make($this->whenLoaded('uom')),
            'description' => $this->description,
            'quantity' => (float) $this->quantity,
            'delivered_qty' => (float) $this->delivered_qty,
            'remaining_qty' => $this->getRemainingQty(),
            'unit_price' => (float) $this->unit_price,
            'discount_percent' => (float) $this->discount_percent,
            'tax_rate' => (float) $this->tax_rate,
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'delivery_date' => $this->delivery_date?->toDateString(),
        ];
    }
}
