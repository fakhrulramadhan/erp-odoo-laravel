<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetDepreciationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'depreciation_date' => $this->depreciation_date?->toDateString(),
            'period' => $this->period,
            'method' => $this->method?->value ?? $this->method,
            'amount' => (float) $this->amount,
            'accumulated_before' => (float) $this->accumulated_before,
            'accumulated_after' => (float) $this->accumulated_after,
            'book_value_before' => (float) $this->book_value_before,
            'book_value_after' => (float) $this->book_value_after,
            'journal_entry_id' => $this->journal_entry_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
