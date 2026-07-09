<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierQuotation extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'quotation_number', 'vendor_id', 'company_id', 'currency_id',
        'status', 'quotation_date', 'validity_date', 'expected_date',
        'exchange_rate', 'subtotal', 'tax_total', 'discount_total', 'total',
        'payment_term', 'notes', 'internal_notes',
        'vendor_price', 'vendor_lead_days', 'vendor_notes',
        'purchase_order_id',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'quotation_date' => 'date',
            'validity_date' => 'date',
            'expected_date' => 'date',
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'vendor_price' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lines(): HasMany { return $this->hasMany(SupplierQuotationLine::class)->orderBy('line_number'); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeByVendor($query, int $vendorId) { return $query->where('vendor_id', $vendorId); }

    public function canTransitionTo(QuotationStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(QuotationStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}"
            );
        }
        return $this->update(['status' => $newStatus]);
    }

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
}
