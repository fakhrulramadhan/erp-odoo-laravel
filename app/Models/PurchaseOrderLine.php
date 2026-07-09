<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'product_id', 'uom_id', 'tax_id',
        'line_number', 'description',
        'quantity', 'received_qty', 'price',
        'tax_rate', 'discount_percent',
        'subtotal', 'tax_amount', 'discount_amount', 'total',
        'delivery_date', 'analytic_account',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'received_qty' => 'decimal:4',
            'price' => 'decimal:4',
            'tax_rate' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_date' => 'date',
        ];
    }

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class); }
    public function tax(): BelongsTo { return $this->belongsTo(TaxSetting::class, 'tax_id'); }

    // ── Calculations ───────────────────────────────
    public function calculateTotals(): static
    {
        $this->subtotal = $this->quantity * $this->price;
        $this->discount_amount = $this->subtotal * ($this->discount_percent / 100);
        $baseAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $baseAmount * ($this->tax_rate / 100);
        $this->total = $baseAmount + $this->tax_amount;

        return $this;
    }

    public function getRemainingQty(): float
    {
        return max(0, (float) ($this->quantity - $this->received_qty));
    }

    public function isFullyReceived(): bool
    {
        return $this->received_qty >= $this->quantity;
    }
}
