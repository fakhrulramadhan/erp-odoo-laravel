<?php

namespace App\Models;

use App\Enums\FinanceStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'payment_number', 'payment_type', 'customer_id', 'vendor_id', 'currency_id',
        'payment_method_id', 'bank_account_id', 'cash_account_id', 'payment_date', 'payment_method', 'status',
        'amount', 'allocated_amount', 'balance_amount', 'reference', 'notes', 'created_by', 'updated_by',
        'posted_by', 'posted_at', 'source_type', 'source_id'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'posted_at' => 'datetime',
        'status' => FinanceStatus::class,
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
