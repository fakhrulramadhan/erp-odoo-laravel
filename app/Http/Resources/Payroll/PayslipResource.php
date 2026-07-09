<?php

namespace App\Http\Resources\Payroll;

use App\Http\Resources\HRM\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payslip_number' => $this->payslip_number,
            'payroll_id' => $this->payroll_id,
            'payroll' => new PayrollResource($this->whenLoaded('payroll')),
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'basic_salary' => (float) $this->basic_salary,
            'total_allowances' => (float) $this->total_allowances,
            'total_deductions' => (float) $this->total_deductions,
            'total_overtime' => (float) $this->total_overtime,
            'total_bonus' => (float) $this->total_bonus,
            'total_tax' => (float) $this->total_tax,
            'total_insurance' => (float) $this->total_insurance,
            'gross_salary' => (float) $this->gross_salary,
            'net_salary' => (float) $this->net_salary,
            'work_days' => $this->work_days,
            'late_minutes' => $this->late_minutes,
            'overtime_hours' => $this->overtime_hours,
            'unpaid_leave_days' => $this->unpaid_leave_days,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
