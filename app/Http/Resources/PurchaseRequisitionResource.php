<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequisitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'requisition_number' => $this->requisition_number,
            'requisition_date' => $this->requisition_date?->toDateString(),
            'required_date' => $this->required_date?->toDateString(),
            'status' => $this->status,
            'purpose' => $this->purpose,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department') ? [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ] : null,
            'currency_id' => $this->currency_id,
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'approved_by' => $this->approved_by,
            'approver' => new UserResource($this->whenLoaded('approver')),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'purchase_order_id' => $this->purchase_order_id,
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'created_by' => $this->created_by,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'lines' => PurchaseRequisitionLineResource::collection($this->whenLoaded('lines')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
