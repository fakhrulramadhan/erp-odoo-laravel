<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'order_number', 'pos_session_id', 'customer_id', 'warehouse_id',
        'subtotal', 'discount_amount', 'tax_amount', 'total',
        'paid', 'change_amount', 'status',
        'sales_order_id', 'invoice_id',
        'company_id', 'branch_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────
    public function posSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(PosOrderLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class);
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
