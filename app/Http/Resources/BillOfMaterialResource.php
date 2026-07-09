<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillOfMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bom_number' => $this->bom_number,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'quantity' => (float) $this->quantity,
            'routing_id' => $this->routing_id,
            'routing' => new RoutingResource($this->whenLoaded('routing')),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'description' => $this->description,
            'is_default' => (bool) $this->is_default,
            'estimated_cost' => (float) ($this->estimated_cost ?? 0),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'lines' => BomLineResource::collection($this->whenLoaded('lines')),
            'revisions' => BomRevisionResource::collection($this->whenLoaded('revisions')),
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
