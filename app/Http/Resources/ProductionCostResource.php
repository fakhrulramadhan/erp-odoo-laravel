<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionCostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'manufacturing_order_id' => $this->manufacturing_order_id,
            'cost_type' => $this->cost_type,
            'description' => $this->description,
            'quantity' => (float) ($this->quantity ?? 0),
            'unit_cost' => (float) ($this->unit_cost ?? 0),
            'total_cost' => (float) ($this->total_cost ?? 0),
            'product_id' => $this->product_id,
            'work_center_id' => $this->work_center_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
