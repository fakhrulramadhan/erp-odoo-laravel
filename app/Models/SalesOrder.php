<?php

namespace App\Models;

use App\Enums\SalesOrderStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'order_number', 'customer_id', 'company_id', 'branch_id', 'currency_id', 'warehouse_id',
        'status', 'order_date', 'delivery_date', 'effective_date',
        'subtotal', 'tax_total', 'discount_total', 'total',
        'notes', 'internal_notes',
        'confirmed_by', 'confirmed_at', 'cancelled_by', 'cancelled_at',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => SalesOrderStatus::class,
            'order_date' => 'date',
            'delivery_date' => 'date',
            'effective_date' => 'date',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function confirmer(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lines(): HasMany { return $this->hasMany(SalesOrderLine::class)->orderBy('line_number'); }
    public function pickings(): HasMany { return $this->hasMany(StockPicking::class, 'sales_order_id'); }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class, 'source_id')
            ->where('source_type', 'sales_order')
            ->where('invoice_type', 'customer');
    }

    public function scopeByStatus($query, SalesOrderStatus $status) { return $query->where('status', $status); }

    public function canTransitionTo(SalesOrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(SalesOrderStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException("Cannot transition from {$this->status->value} to {$newStatus->value}");
        }

        return $this->update(['status' => $newStatus]);
    }

    public function recalculate(): void
    {
        $this->load('lines');
        $subtotal = $this->lines->sum('subtotal');
        $discount = $this->lines->sum('discount_amount');
        $tax = $this->lines->sum('tax_amount');

        $this->update([
            'subtotal' => round((float) $subtotal, 2),
            'discount_total' => round((float) $discount, 2),
            'tax_total' => round((float) $tax, 2),
            'total' => round((float) $subtotal - (float) $discount + (float) $tax, 2),
        ]);
    }

    public function getTotalOrderedQty(): float
    {
        return (float) $this->lines->sum('quantity');
    }

    public function getTotalDeliveredQty(): float
    {
        return (float) $this->lines->sum('delivered_qty');
    }
}
