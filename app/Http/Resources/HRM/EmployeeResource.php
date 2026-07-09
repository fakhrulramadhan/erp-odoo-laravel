<?php

namespace App\Http\Resources\HRM;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_number' => $this->employee_number,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'country' => $this->country,
            'national_id' => $this->national_id,
            'tax_id' => $this->tax_id,
            'bank_account_number' => $this->bank_account_number,
            'bank_name' => $this->bank_name,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'department_id' => $this->department_id,
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'position_id' => $this->position_id,
            'position' => $this->whenLoaded('position', fn() => [
                'id' => $this->position->id,
                'name' => $this->position->name,
            ]),
            'employment_type' => $this->employment_type,
            'status' => $this->status,
            'hire_date' => $this->hire_date?->toDateString(),
            'confirmation_date' => $this->confirmation_date?->toDateString(),
            'resignation_date' => $this->resignation_date?->toDateString(),
            'resignation_reason' => $this->resignation_reason,
            'basic_salary' => (float) $this->basic_salary,
            'manager_id' => $this->manager_id,
            'manager' => $this->whenLoaded('manager', fn() => [
                'id' => $this->manager->id,
                'full_name' => $this->manager->full_name,
            ]),
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'photo' => $this->photo,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
