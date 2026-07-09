<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'type'             => $this->type,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'billing_address'  => $this->billing_address,
            'shipping_address' => $this->shipping_address,
            'city'             => $this->city,
            'state'            => $this->state,
            'zip_code'         => $this->zip_code,
            'country'          => $this->country,
            'tax_number'       => $this->tax_number,
            'contact_person'   => $this->contact_person,
            'payment_term'     => $this->payment_term,
            'credit_limit'     => $this->credit_limit,
            'current_balance'  => $this->current_balance,
            'notes'            => $this->notes,
            'is_active'        => $this->is_active,
            'creator'          => new UserResource($this->whenLoaded('creator')),
            'created_at'       => $this->created_at?->toISOString(),
            'updated_at'       => $this->updated_at?->toISOString(),
        ];
    }
}
