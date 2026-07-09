<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'company_id' => $this->company_id,
            'address' => $this->address,
            'is_main' => (bool) $this->is_main,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
