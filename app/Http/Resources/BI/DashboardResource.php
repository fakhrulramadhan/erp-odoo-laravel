<?php

namespace App\Http\Resources\BI;

use App\Http\Resources\UserResource;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'is_default' => $this->is_default,
            'is_public' => $this->is_public,
            'layout' => $this->layout,
            'widgets' => $this->whenLoaded('widgets'),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
