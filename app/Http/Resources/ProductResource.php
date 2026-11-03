<?php

namespace App\Http\Resources;

use App\Http\Resources\V1\ProductCategoryResource;
use App\Http\Resources\V1\UnitOfMeasureResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category' => new ProductCategoryResource($this->whenLoaded('category')),
            'uom_id' => $this->uom_id,
            'uom' => new UnitOfMeasureResource($this->whenLoaded('uom')),
            'tax_id' => $this->tax_id,
            'tax' => $this->whenLoaded('tax'),
            'type' => $this->type,
            'purchase_price' => (float) ($this->purchase_price ?? 0),
            'sales_price' => (float) ($this->sales_price ?? 0),
            'minimum_stock' => (float) ($this->minimum_stock ?? 0),
            'image' => $this->image,
            'weight' => $this->weight,
            'volume' => $this->volume,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
