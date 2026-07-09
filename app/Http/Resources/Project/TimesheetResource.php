<?php

namespace App\Http\Resources\Project;

use App\Http\Resources\HRM\EmployeeResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimesheetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'timesheet_number' => $this->timesheet_number,
            'employee_id' => $this->employee_id,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'task_id' => $this->task_id,
            'task' => new TaskResource($this->whenLoaded('task')),
            'work_date' => $this->work_date?->toDateString(),
            'hours' => (float) $this->hours,
            'description' => $this->description,
            'is_billable' => $this->is_billable,
            'status' => $this->status,
            'approved_by' => $this->approved_by,
            'approved_by_user' => new UserResource($this->whenLoaded('approvedBy')),
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
