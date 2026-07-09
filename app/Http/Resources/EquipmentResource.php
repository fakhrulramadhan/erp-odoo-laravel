<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EquipmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'work_center_id' => $this->work_center_id,
            'work_center' => new WorkCenterResource($this->whenLoaded('workCenter')),
            'asset_id' => $this->asset_id,
            'branch_id' => $this->branch_id,
            'location' => $this->location,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'purchase_date' => $this->purchase_date?->toDateString(),
            'warranty_expiry' => $this->warranty_expiry?->toDateString(),
            'maintenance_interval_days' => $this->maintenance_interval_days,
            'last_maintenance_date' => $this->last_maintenance_date?->toDateString(),
            'next_maintenance_date' => $this->next_maintenance_date?->toDateString(),
            'status' => $this->status,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
