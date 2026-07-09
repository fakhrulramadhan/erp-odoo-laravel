<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'code', 'name', 'type', 'bank_name', 'account_number', 'account_name',
        'currency_id', 'opening_balance', 'current_balance', 'description',
        'is_active', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'bank_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCash($query)
    {
        return $query->where('type', 'cash');
    }

    public function scopeBank($query)
    {
        return $query->where('type', 'bank');
    }
}
