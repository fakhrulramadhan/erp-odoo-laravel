<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'project_id', 'name', 'description', 'due_date', 'completed_date',
        'is_completed', 'sequence', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_date' => 'date',
        'is_completed' => 'boolean',
        'sequence' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
