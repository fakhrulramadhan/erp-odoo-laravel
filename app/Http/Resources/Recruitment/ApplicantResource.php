<?php

namespace App\Http\Resources\Recruitment;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'applicant_number' => $this->applicant_number,
            'job_vacancy_id' => $this->job_vacancy_id,
            'job_vacancy' => new JobVacancyResource($this->whenLoaded('jobVacancy')),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'resume_path' => $this->resume_path,
            'stage' => $this->stage,
            'score' => $this->score,
            'notes' => $this->notes,
            'assigned_to' => $this->assigned_to,
            'assigned_user' => new UserResource($this->whenLoaded('assignedUser')),
            'employee_id' => $this->employee_id,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'interviews' => InterviewResource::collection($this->whenLoaded('interviews')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
