<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QualityCheckResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'check_number' => $this->check_number,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'quantity' => (float) $this->quantity,
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'lot_id' => $this->lot_id,
            'lot' => new LotResource($this->whenLoaded('lot')),
            'location_id' => $this->location_id,
            'quarantine_location_id' => $this->quarantine_location_id,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'result' => $this->result,
            'rejected_qty' => (float) ($this->rejected_qty ?? 0),
            'defect_type' => $this->defect_type,
            'corrective_action' => $this->corrective_action,
            'inspector_id' => $this->inspector_id,
            'inspector' => new UserResource($this->whenLoaded('inspector')),
            'inspection_date' => $this->inspection_date?->toIso8601String(),
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toIso8601String(),
            'notes' => $this->notes,
            'lines' => QualityCheckLineResource::collection($this->whenLoaded('lines')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
