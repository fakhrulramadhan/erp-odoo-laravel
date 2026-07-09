<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lot_number' => $this->lot_number,
            'product_id' => $this->product_id,
            'tracking_type' => $this->tracking_type,
            'expiration_date' => $this->expiration_date?->toDateString(),
            'manufacture_date' => $this->manufacture_date?->toDateString(),
            'is_active' => (bool) $this->is_active,
        ];
    }
}
