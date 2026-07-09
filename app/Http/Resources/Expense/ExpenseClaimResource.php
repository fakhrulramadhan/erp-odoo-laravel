<?php

namespace App\Http\Resources\Expense;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseClaimResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_number' => $this->expense_number,
            'employee_id' => $this->employee_id,
            'employee' => new UserResource($this->whenLoaded('employee')),
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', fn() => [
                'id' => $this->project->id,
                'name' => $this->project->name,
            ]),
            'expense_date' => $this->expense_date?->toDateString(),
            'total_amount' => (float) $this->total_amount,
            'status' => $this->status,
            'description' => $this->description,
            'approved_by' => $this->approved_by,
            'approved_by_user' => new UserResource($this->whenLoaded('approvedBy')),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'journal_entry_id' => $this->journal_entry_id,
            'payment_id' => $this->payment_id,
            'lines' => $this->whenLoaded('lines'),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
