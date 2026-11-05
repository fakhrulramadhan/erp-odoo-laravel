<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StockPickingResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'order_date' => $this->order_date?->toDateString(),
            'expected_date' => $this->expected_date?->toDateString(),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'notes' => $this->notes,
            'internal_notes' => $this->internal_notes,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) ($this->tax_total ?? 0),
            'discount_amount' => (float) ($this->discount_total ?? 0),
            'total_amount' => (float) ($this->total ?? 0),
            'currency_id' => $this->currency_id,
            'currency' => CurrencyResource::make($this->whenLoaded('currency')),
            'vendor_id' => $this->vendor_id,
            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'company_id' => $this->company_id,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => WarehouseResource::make($this->whenLoaded('warehouse')),
            'approved_by' => $this->approved_by,
            'approver' => UserResource::make($this->whenLoaded('approver')),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'cancelled_by' => $this->cancelled_by,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'lines' => PurchaseOrderLineResource::collection($this->whenLoaded('lines')),
            'pickings' => StockPickingResource::collection($this->whenLoaded('pickings')),
            'total_ordered_qty' => (float) $this->getTotalOrderedQty(),
            'total_received_qty' => (float) $this->getTotalReceivedQty(),
            'is_fully_received' => $this->isFullyReceived(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
