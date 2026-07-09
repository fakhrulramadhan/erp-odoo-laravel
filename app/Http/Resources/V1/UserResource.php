<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'phone' => $this->phone,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch' => $this->whenLoaded('branch', fn() => [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
                'code' => $this->branch->code,
            ]),
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'position' => $this->whenLoaded('position', fn() => [
                'id' => $this->position->id,
                'name' => $this->position->name,
            ]),
            'roles' => $this->whenLoaded('roles', fn() => $this->roles->pluck('name')),
            'permissions' => $this->when(
                $this->relationLoaded('roles'),
                fn() => $this->getAllPermissions()->pluck('name')
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
