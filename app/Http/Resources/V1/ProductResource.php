<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'barcode'        => $this->barcode,
            'name'           => $this->name,
            'description'    => $this->description,
            'category_id'    => $this->category_id,
            'category'       => new ProductCategoryResource($this->whenLoaded('category')),
            'uom_id'         => $this->uom_id,
            'uom'            => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'type'           => $this->type,
            'purchase_price' => $this->purchase_price,
            'sales_price'    => $this->sales_price,
            'tax_id'         => $this->tax_id,
            'tax'            => $this->whenLoaded('tax'),
            'minimum_stock'  => $this->minimum_stock,
            'image'          => $this->image,
            'weight'         => $this->weight,
            'volume'         => $this->volume,
            'is_active'      => $this->is_active,
            'creator'        => new UserResource($this->whenLoaded('creator')),
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
