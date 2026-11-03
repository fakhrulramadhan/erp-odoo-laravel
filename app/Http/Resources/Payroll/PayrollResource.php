<?php

namespace App\Http\Resources\Payroll;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_number' => $this->payroll_number,
            'payroll_period_id' => $this->payroll_period_id,
            'payroll_period' => PayrollPeriodResource::make($this->whenLoaded('payrollPeriod')),
            'company_id' => $this->company_id,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => BranchResource::make($this->whenLoaded('branch')),
            'pay_date' => $this->pay_date?->toDateString(),
            'status' => $this->status,
            'total_employees' => $this->total_employees,
            'total_gross' => (float) $this->total_gross,
            'total_deductions' => (float) $this->total_deductions,
            'total_net' => (float) $this->total_net,
            'notes' => $this->notes,
            'approved_by' => $this->approved_by,
            'approved_by_user' => UserResource::make($this->whenLoaded('approvedBy')),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'payslips' => PayslipResource::collection($this->whenLoaded('payslips')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
