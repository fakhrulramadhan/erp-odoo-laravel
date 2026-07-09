<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'asset_account_id' => $this->asset_account_id,
            'depreciation_expense_account_id' => $this->depreciation_expense_account_id,
            'accumulated_depreciation_account_id' => $this->accumulated_depreciation_account_id,
            'disposal_account_id' => $this->disposal_account_id,
            'default_useful_life_months' => $this->default_useful_life_months,
            'default_depreciation_method' => $this->default_depreciation_method,
            'default_residual_rate' => $this->default_residual_rate !== null ? (float) $this->default_residual_rate : null,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
