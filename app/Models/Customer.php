<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'code', 'name', 'type', 'email', 'phone',
        'billing_address', 'shipping_address', 'city', 'state', 'zip_code', 'country',
        'tax_number', 'contact_person', 'payment_term', 'credit_limit', 'current_balance',
        'notes', 'is_active', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
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

    public function scopeCompany($query)
    {
        return $query->where('type', 'company');
    }

    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }
}
