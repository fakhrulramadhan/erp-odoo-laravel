<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'barcode' => $this->barcode,
            'product_category_id' => $this->product_category_id,
            'productCategory' => $this->whenLoaded('productCategory', fn() => ['id' => $this->productCategory->id, 'name' => $this->productCategory->name]),
            'unit_of_measure_id' => $this->unit_of_measure_id,
            'unitOfMeasure' => $this->whenLoaded('unitOfMeasure', fn() => ['id' => $this->unitOfMeasure->id, 'name' => $this->unitOfMeasure->name, 'symbol' => $this->unitOfMeasure->symbol]),
            'description' => $this->description,
            'cost' => (float) ($this->cost ?? 0),
            'price' => (float) ($this->price ?? 0),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
