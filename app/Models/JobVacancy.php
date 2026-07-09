<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobVacancy extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'vacancy_number', 'title', 'department_id', 'position_id',
        'employment_type', 'number_of_openings', 'salary_min', 'salary_max',
        'description', 'requirements', 'posting_date', 'closing_date', 'status',
        'company_id', 'branch_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'number_of_openings' => 'integer',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
        'posting_date' => 'date',
        'closing_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
