<?php

namespace App\Http\Resources\CMS;

use App\Http\Resources\UserResource;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'featured_image' => $this->featured_image,
            'template' => $this->template,
            'status' => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'author_id' => $this->author_id,
            'author' => new UserResource($this->whenLoaded('author')),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
