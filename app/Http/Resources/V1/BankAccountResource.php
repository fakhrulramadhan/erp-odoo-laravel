<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankAccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'code'            => $this->code,
            'name'            => $this->name,
            'type'            => $this->type,
            'bank_name'       => $this->bank_name,
            'account_number'  => $this->account_number,
            'account_name'    => $this->account_name,
            'currency_id'     => $this->currency_id,
            'currency'        => $this->whenLoaded('currency'),
            'opening_balance' => $this->opening_balance,
            'current_balance' => $this->current_balance,
            'description'     => $this->description,
            'is_active'       => $this->is_active,
            'creator'         => new UserResource($this->whenLoaded('creator')),
            'created_at'      => $this->created_at?->toISOString(),
            'updated_at'      => $this->updated_at?->toISOString(),
        ];
    }
}
