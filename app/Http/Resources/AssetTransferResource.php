<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetTransferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'transfer_date' => $this->transfer_date?->toDateString(),
            'from_branch_id' => $this->from_branch_id,
            'from_department_id' => $this->from_department_id,
            'to_branch_id' => $this->to_branch_id,
            'to_department_id' => $this->to_department_id,
            'to_location' => $this->to_location,
            'to_custodian_id' => $this->to_custodian_id,
            'reason' => $this->reason,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
