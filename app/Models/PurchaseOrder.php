<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'order_number', 'vendor_id', 'company_id', 'branch_id', 'currency_id',
        'warehouse_id', 'status', 'order_date', 'expected_date', 'effective_date',
        'exchange_rate', 'subtotal', 'tax_total', 'discount_total', 'total',
        'payment_term', 'notes', 'internal_notes',
        'approved_by', 'approved_at', 'origin',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PurchaseOrderStatus::class,
            'order_date' => 'date',
            'expected_date' => 'date',
            'effective_date' => 'date',
            'approved_at' => 'datetime',
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    // ── Relationships ──────────────────────────────
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function lines(): HasMany { return $this->hasMany(PurchaseOrderLine::class)->orderBy('line_number'); }
    public function pickings(): HasMany { return $this->hasMany(StockPicking::class); }

    // ── Scopes ─────────────────────────────────────
    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeDraft($query) { return $query->where('status', PurchaseOrderStatus::Draft); }
    public function scopeOrdered($query) { return $query->where('status', PurchaseOrderStatus::Ordered); }
    public function scopeByVendor($query, int $vendorId) { return $query->where('vendor_id', $vendorId); }
    public function scopeByCompany($query, int $companyId) { return $query->where('company_id', $companyId); }

    // ── State Machine ──────────────────────────────
    public function canTransitionTo(PurchaseOrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(PurchaseOrderStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}"
            );
        }
        return $this->update(['status' => $newStatus]);
    }

    // ── Calculations ───────────────────────────────
    public function recalculate(): void
    {
        $this->load('lines');
        $subtotal = $this->lines->sum('subtotal');
        $taxTotal = $this->lines->sum('tax_amount');
        $discountTotal = $this->lines->sum('discount_amount');

        $this->update([
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'total' => $subtotal + $taxTotal - $discountTotal,
        ]);
    }

    public function getTotalReceivedQty(): float
    {
        return (float) $this->lines->sum('received_qty');
    }

    public function getTotalOrderedQty(): float
    {
        return (float) $this->lines->sum('quantity');
    }

    public function isFullyReceived(): bool
    {
        $this->load('lines');
        return $this->lines->every(fn($line) => $line->received_qty >= $line->quantity);
    }
}
