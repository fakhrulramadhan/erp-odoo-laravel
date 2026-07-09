<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'payslip_number', 'payroll_id', 'employee_id',
        'basic_salary', 'total_allowances', 'total_deductions',
        'total_overtime', 'total_bonus', 'total_tax', 'total_insurance',
        'gross_salary', 'net_salary',
        'work_days', 'late_minutes', 'overtime_hours', 'unpaid_leave_days',
        'status', 'notes', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_overtime' => 'decimal:2',
        'total_bonus' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_insurance' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'work_days' => 'integer',
        'late_minutes' => 'integer',
        'overtime_hours' => 'integer',
        'unpaid_leave_days' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PayslipLine::class);
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
