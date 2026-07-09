<?php

namespace App\Http\Resources\Ecommerce;

use App\Http\Resources\CustomerResource;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EcommerceOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'discount_amount' => (float) $this->discount_amount,
            'tax_amount' => (float) $this->tax_amount,
            'total' => (float) $this->total,
            'shipping_name' => $this->shipping_name,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_phone' => $this->shipping_phone,
            'shipping_method_id' => $this->shipping_method_id,
            'shipping_method' => $this->whenLoaded('shippingMethod'),
            'tracking_number' => $this->tracking_number,
            'notes' => $this->notes,
            'sales_order_id' => $this->sales_order_id,
            'invoice_id' => $this->invoice_id,
            'payment_id' => $this->payment_id,
            'promo_code_id' => $this->promo_code_id,
            'promo_code' => $this->whenLoaded('promoCode'),
            'lines' => $this->whenLoaded('lines'),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
