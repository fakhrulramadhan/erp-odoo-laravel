<?php

namespace App\Http\Resources\Payroll;

use App\Http\Resources\HRM\EmployeeResource;
use App\Http\Resources\CompanyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'salary_structure_id' => $this->salary_structure_id,
            'salary_structure' => new SalaryStructureResource($this->whenLoaded('salaryStructure')),
            'basic_salary' => (float) $this->basic_salary,
            'gross_salary' => (float) $this->gross_salary,
            'net_salary' => (float) $this->net_salary,
            'effective_date' => $this->effective_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_active' => $this->is_active,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
