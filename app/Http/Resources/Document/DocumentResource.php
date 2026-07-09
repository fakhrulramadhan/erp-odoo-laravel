<?php

namespace App\Http\Resources\Document;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_number' => $this->document_number,
            'title' => $this->title,
            'description' => $this->description,
            'folder_id' => $this->folder_id,
            'folder' => $this->whenLoaded('folder', fn() => [
                'id' => $this->folder->id,
                'name' => $this->folder->name,
            ]),
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_size' => $this->file_size,
            'status' => $this->status,
            'documentable_type' => $this->documentable_type,
            'documentable_id' => $this->documentable_id,
            'owner_id' => $this->owner_id,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'access_level' => $this->access_level,
            'expiry_date' => $this->expiry_date?->toDateString(),
            'versions' => $this->whenLoaded('versions'),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
