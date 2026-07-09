<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowAction extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'workflow_step_id', 'action_type', 'action_config', 'sequence',
    ];

    protected $casts = [
        'action_config' => 'array',
        'sequence' => 'integer',
    ];

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }
}
