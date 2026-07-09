<?php

namespace App\Http\Resources\Ecommerce;

use App\Http\Resources\ProductResource;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EcommerceProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'long_description' => $this->long_description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'is_featured' => $this->is_featured,
            'is_visible' => $this->is_visible,
            'compare_price' => (float) $this->compare_price,
            'stock_quantity' => $this->stock_quantity,
            'weight' => (float) $this->weight,
            'attributes' => $this->attributes,
            'sequence' => $this->sequence,
            'images' => $this->whenLoaded('images'),
            'variants' => $this->whenLoaded('variants'),
            'reviews' => ProductReviewResource::collection($this->whenLoaded('reviews')),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
