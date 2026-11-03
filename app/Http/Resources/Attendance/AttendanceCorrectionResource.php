<?php

namespace App\Http\Resources\Attendance;

use App\Http\Resources\HRM\EmployeeResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceCorrectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attendance_id' => $this->attendance_id,
            'attendance' => AttendanceResource::make($this->whenLoaded('attendance')),
            'employee_id' => $this->employee_id,
            'employee' => EmployeeResource::make($this->whenLoaded('employee')),
            'corrected_check_in' => $this->corrected_check_in?->toIso8601String(),
            'corrected_check_out' => $this->corrected_check_out?->toIso8601String(),
            'reason' => $this->reason,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_by_user' => UserResource::make($this->whenLoaded('approvedBy')),
            'rejection_reason' => $this->rejection_reason,
            'company_id' => $this->company_id,
            'company' => CompanyResource::make($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
