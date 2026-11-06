<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_number' => $this->asset_number,
            'name' => $this->name,
            'description' => $this->description,
            'asset_category_id' => $this->category_id,
            'category' => AssetCategoryResource::make($this->whenLoaded('category')),
            'acquisition_date' => $this->purchase_date?->toDateString(),
            'acquisition_cost' => (float) ($this->purchase_value ?? 0),
            'acquisition_value' => (float) ($this->purchase_value ?? 0),
            'salvage_value' => (float) ($this->residual_value ?? 0),
            'residual_value' => (float) ($this->residual_value ?? 0),
            'purchase_date' => $this->purchase_date?->toDateString(),
            'purchase_value' => (float) ($this->purchase_value ?? 0),
            'useful_life_months' => $this->useful_life_months,
            'depreciation_method' => $this->depreciation_method,
            'depreciation_method_label' => $this->depreciation_method_label,
            'start_depreciation_date' => $this->commission_date?->toDateString(),
            'commission_date' => $this->commission_date?->toDateString(),
            'accumulated_depreciation' => (float) ($this->accumulated_depreciation ?? 0),
            'book_value' => $this->getBookValue(),
            'current_value' => (float) ($this->current_value ?? 0),
            'status' => $this->status?->value ?? $this->status,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'branch_id' => $this->branch_id,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'department_id' => $this->department_id,
            'location' => $this->location,
            'custodian_id' => $this->custodian_id,
            'serial_number' => $this->serial_number,
            'disposal_date' => $this->disposal_date?->toDateString(),
            'disposal_amount' => $this->disposal_value !== null ? (float) $this->disposal_value : null,
            'disposal_reason' => $this->disposal_reason,
            'monthly_depreciation' => $this->getMonthlyDepreciation(),
            'notes' => $this->notes,
            'depreciations' => AssetDepreciationResource::collection($this->whenLoaded('depreciations')),
            'transfers' => AssetTransferResource::collection($this->whenLoaded('transfers')),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
