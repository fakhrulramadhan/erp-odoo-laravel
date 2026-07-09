<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockPickingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'picking_number' => $this->picking_number,
            'picking_type' => $this->picking_type?->value ?? $this->picking_type,
            'picking_type_label' => $this->picking_type?->label(),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'origin' => $this->origin,
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'company_id' => $this->company_id,
            'source_location_id' => $this->source_location_id,
            'source_location' => new StockLocationResource($this->whenLoaded('sourceLocation')),
            'destination_location_id' => $this->destination_location_id,
            'destination_location' => new StockLocationResource($this->whenLoaded('destinationLocation')),
            'vendor_id' => $this->vendor_id,
            'vendor' => new VendorResource($this->whenLoaded('vendor')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'purchase_order_id' => $this->purchase_order_id,
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'effective_date' => $this->effective_date?->toDateString(),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'moves' => StockMoveResource::collection($this->whenLoaded('moves')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
