<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollComponent extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'name', 'code', 'type', 'calculation_type', 'default_amount',
        'percentage', 'based_on', 'is_taxable', 'is_active', 'description',
        'company_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function structureLines(): HasMany
    {
        return $this->hasMany(SalaryStructureLine::class);
    }

    public function payslipLines(): HasMany
    {
        return $this->hasMany(PayslipLine::class);
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
