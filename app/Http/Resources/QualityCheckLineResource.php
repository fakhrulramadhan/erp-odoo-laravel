<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QualityCheckLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quality_check_id' => $this->quality_check_id,
            'line_number' => $this->line_number,
            'control_point' => $this->control_point,
            'method' => $this->method,
            'expected_value' => $this->expected_value,
            'actual_value' => $this->actual_value,
            'tolerance_min' => $this->tolerance_min !== null ? (float) $this->tolerance_min : null,
            'tolerance_max' => $this->tolerance_max !== null ? (float) $this->tolerance_max : null,
            'is_pass' => $this->is_pass,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
