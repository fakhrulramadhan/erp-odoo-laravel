<?php

namespace App\Http\Resources\Sales;

use App\Http\Resources\{CustomerResource, CurrencyResource, WarehouseResource, UserResource};
use App\Http\Resources\Finance\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date?->toDateString(),
            'delivery_date' => $this->delivery_date?->toDateString(),
            'effective_date' => $this->effective_date?->toDateString(),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_total,
            'discount_amount' => (float) $this->discount_total,
            'total_amount' => (float) $this->total,
            'currency_id' => $this->currency_id,
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'customer_id' => $this->customer_id,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'company_id' => $this->company_id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'confirmed_by' => $this->confirmed_by,
            'confirmer' => UserResource::make($this->whenLoaded('confirmer')),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'lines' => SalesOrderLineResource::collection($this->whenLoaded('lines')),
            'invoice' => $this->whenLoaded('invoice', fn () => $this->invoice ? new InvoiceResource($this->invoice) : null),
            'total_ordered_qty' => (float) $this->getTotalOrderedQty(),
            'total_delivered_qty' => (float) $this->getTotalDeliveredQty(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
