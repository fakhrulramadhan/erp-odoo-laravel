<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturingOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'bom_id' => $this->bom_id,
            'bom' => new BillOfMaterialResource($this->whenLoaded('bom')),
            'routing_id' => $this->routing_id,
            'routing' => new RoutingResource($this->whenLoaded('routing')),
            'quantity' => (float) $this->quantity,
            'produced_qty' => (float) ($this->produced_qty ?? 0),
            'scrap_qty' => (float) ($this->scrap_qty ?? 0),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'work_center_id' => $this->work_center_id,
            'work_center' => new WorkCenterResource($this->whenLoaded('workCenter')),
            'source_location_id' => $this->source_location_id,
            'destination_location_id' => $this->destination_location_id,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'planned_start' => $this->planned_start?->toDateString(),
            'planned_finish' => $this->planned_finish?->toDateString(),
            'actual_start' => $this->actual_start?->toIso8601String(),
            'actual_finish' => $this->actual_finish?->toIso8601String(),
            'deadline' => $this->deadline?->toDateString(),
            'priority' => $this->priority,
            'unit_cost' => (float) ($this->unit_cost ?? 0),
            'total_cost' => (float) ($this->total_cost ?? 0),
            'sale_order_id' => $this->sale_order_id,
            'notes' => $this->notes,
            'lines' => ManufacturingOrderLineResource::collection($this->whenLoaded('lines')),
            'work_orders' => WorkOrderResource::collection($this->whenLoaded('workOrders')),
            'production_costs' => ProductionCostResource::collection($this->whenLoaded('productionCosts')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
