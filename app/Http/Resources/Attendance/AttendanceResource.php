<?php

namespace App\Http\Resources\Attendance;

use App\Http\Resources\HRM\EmployeeResource;
use App\Http\Resources\HRM\ShiftResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'attendance_date' => $this->attendance_date?->toDateString(),
            'check_in' => $this->check_in?->toIso8601String(),
            'check_out' => $this->check_out?->toIso8601String(),
            'status' => $this->status,
            'late_minutes' => $this->late_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'work_hours' => $this->work_hours,
            'check_in_location' => $this->check_in_location,
            'check_out_location' => $this->check_out_location,
            'notes' => $this->notes,
            'shift_id' => $this->shift_id,
            'shift' => new ShiftResource($this->whenLoaded('shift')),
            'approved_by' => $this->approved_by,
            'approved_by_user' => new UserResource($this->whenLoaded('approvedBy')),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
