<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'name'           => $this->name,
            'type'           => $this->type,
            'bank_account_id' => $this->bank_account_id,
            'bank_account'   => new BankAccountResource($this->whenLoaded('bankAccount')),
            'description'    => $this->description,
            'is_active'      => $this->is_active,
            'creator'        => new UserResource($this->whenLoaded('creator')),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
