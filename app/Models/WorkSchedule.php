<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'name', 'start_time', 'end_time', 'work_hours_per_day',
        'is_flexible', 'days_of_week',
        'company_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'work_hours_per_day' => 'integer',
        'is_flexible' => 'boolean',
        'days_of_week' => 'array',
    ];

    // ── Relationships ──────────────────────────────────────
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
