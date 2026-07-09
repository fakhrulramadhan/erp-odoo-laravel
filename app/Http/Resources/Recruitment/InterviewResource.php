<?php

namespace App\Http\Resources\Recruitment;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'applicant_id' => $this->applicant_id,
            'applicant' => new ApplicantResource($this->whenLoaded('applicant')),
            'interview_type' => $this->interview_type,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'location' => $this->location,
            'interviewer_id' => $this->interviewer_id,
            'interviewer' => new UserResource($this->whenLoaded('interviewer')),
            'feedback' => $this->feedback,
            'rating' => $this->rating,
            'result' => $this->result,
            'notes' => $this->notes,
            'company_id' => $this->company_id,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
