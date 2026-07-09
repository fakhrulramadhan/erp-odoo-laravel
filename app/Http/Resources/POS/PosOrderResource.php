<?php

namespace App\Http\Resources\POS;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'pos_session_id' => $this->pos_session_id,
            'pos_session' => new PosSessionResource($this->whenLoaded('posSession')),
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'subtotal' => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'paid' => (float) $this->paid,
            'change_amount' => (float) $this->change_amount,
            'status' => $this->status,
            'sales_order_id' => $this->sales_order_id,
            'invoice_id' => $this->invoice_id,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'lines' => $this->whenLoaded('lines'),
            'payments' => $this->whenLoaded('payments'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
