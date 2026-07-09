<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmActivity extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'crm_activities';

    protected $fillable = [
        'activity_type', 'subject', 'description',
        'activityable_type', 'activityable_id',
        'due_date', 'assigned_to', 'is_done', 'completed_at', 'result',
        'company_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'is_done' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────
    public function activityable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
