<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'opportunity_number', 'lead_id', 'customer_id', 'name', 'stage',
        'probability', 'expected_revenue', 'actual_revenue',
        'expected_close_date', 'actual_close_date',
        'assigned_to', 'description', 'notes',
        'company_id', 'branch_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'probability' => 'integer',
        'expected_revenue' => 'decimal:2',
        'actual_revenue' => 'decimal:2',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'activityable_id')
            ->where('activityable_type', 'opportunity');
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
