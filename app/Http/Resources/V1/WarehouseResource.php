<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'code'       => $this->code,
            'name'       => $this->name,
            'address'    => $this->address,
            'city'       => $this->city,
            'phone'      => $this->phone,
            'pic_id'     => $this->pic_id,
            'pic'        => new UserResource($this->whenLoaded('pic')),
            'is_main'    => $this->is_main,
            'is_active'  => $this->is_active,
            'creator'    => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
