<?php

namespace App\Http\Resources\POS;

use App\Http\Resources\UserResource;
use App\Http\Resources\WarehouseResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_number' => $this->session_number,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'warehouse_id' => $this->warehouse_id,
            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'opened_at' => $this->opened_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'opening_cash' => (float) $this->opening_cash,
            'closing_cash' => (float) $this->closing_cash,
            'total_sales' => (float) $this->total_sales,
            'total_cash' => (float) $this->total_cash,
            'total_non_cash' => (float) $this->total_non_cash,
            'cash_difference' => (float) $this->cash_difference,
            'status' => $this->status,
            'notes' => $this->notes,
            'orders' => PosOrderResource::collection($this->whenLoaded('orders')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
