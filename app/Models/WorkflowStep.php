<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowStep extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'workflow_id', 'name', 'type', 'sequence', 'conditions',
        'approver_id', 'approval_level', 'is_required', 'config',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'conditions' => 'array',
        'approval_level' => 'integer',
        'is_required' => 'boolean',
        'config' => 'array',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class);
    }
}
