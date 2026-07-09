<?php

namespace App\Http\Resources\Project;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_number' => $this->task_number,
            'project_id' => $this->project_id,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'parent_id' => $this->parent_id,
            'parent' => $this->whenLoaded('parent', fn() => [
                'id' => $this->parent->id,
                'task_number' => $this->parent->task_number,
                'name' => $this->parent->name,
            ]),
            'milestone_id' => $this->milestone_id,
            'milestone' => new MilestoneResource($this->whenLoaded('milestone')),
            'sprint_id' => $this->sprint_id,
            'sprint' => new SprintResource($this->whenLoaded('sprint')),
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->assigned_to,
            'assignee' => $this->whenLoaded('assignee', fn() => [
                'id' => $this->assignee->id,
                'full_name' => $this->assignee->full_name,
            ]),
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'progress' => (float) $this->progress,
            'sequence' => $this->sequence,
            'task_type' => $this->task_type,
            'children' => TaskResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
