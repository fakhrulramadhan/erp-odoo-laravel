<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipLine extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'payslip_id', 'payroll_component_id', 'component_name',
        'component_type', 'amount', 'sequence',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sequence' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }

    public function payrollComponent(): BelongsTo
    {
        return $this->belongsTo(PayrollComponent::class);
    }
}
