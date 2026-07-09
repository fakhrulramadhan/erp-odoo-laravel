<?php

namespace App\Http\Resources\Payroll;

use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'calculation_type' => $this->calculation_type,
            'default_amount' => (float) $this->default_amount,
            'percentage' => (float) $this->percentage,
            'based_on' => $this->based_on,
            'is_taxable' => $this->is_taxable,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
