<?php

namespace App\Http\Resources\Recruitment;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\BranchResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobVacancyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vacancy_number' => $this->vacancy_number,
            'title' => $this->title,
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
            'number_of_openings' => $this->number_of_openings,
            'salary_min' => (float) $this->salary_min,
            'salary_max' => (float) $this->salary_max,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'posting_date' => $this->posting_date?->toDateString(),
            'closing_date' => $this->closing_date?->toDateString(),
            'status' => $this->status,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'branch_id' => $this->branch_id,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
