<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timesheet extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'timesheet_number', 'employee_id', 'project_id', 'task_id',
        'work_date', 'hours', 'description', 'is_billable', 'status',
        'approved_by', 'company_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
        'is_billable' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
