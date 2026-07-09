<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'project_id', 'employee_id', 'role', 'hourly_rate',
        'joined_date', 'left_date', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'joined_date' => 'date',
        'left_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
