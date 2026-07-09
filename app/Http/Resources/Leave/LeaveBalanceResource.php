<?php

namespace App\Http\Resources\Leave;

use App\Http\Resources\HRM\EmployeeResource;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'leave_type_id' => $this->leave_type_id,
            'leave_type' => new LeaveTypeResource($this->whenLoaded('leaveType')),
            'year' => $this->year,
            'total_days' => (float) $this->total_days,
            'used_days' => (float) $this->used_days,
            'pending_days' => (float) $this->pending_days,
            'remaining_days' => (float) $this->remaining_days,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
