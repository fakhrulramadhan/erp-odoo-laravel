<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierQuotationLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_quotation_id', 'product_id', 'uom_id',
        'line_number', 'description',
        'quantity', 'price', 'tax_rate', 'discount_percent',
        'subtotal', 'tax_amount', 'discount_amount', 'total',
        'vendor_price', 'vendor_qty', 'vendor_lead_days',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'price' => 'decimal:4',
            'tax_rate' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'vendor_price' => 'decimal:4',
            'vendor_qty' => 'decimal:4',
        ];
    }

    public function supplierQuotation(): BelongsTo { return $this->belongsTo(SupplierQuotation::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class); }

    public function calculateTotals(): static
    {
        $this->subtotal = $this->quantity * $this->price;
        $this->discount_amount = $this->subtotal * ($this->discount_percent / 100);
        $baseAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $baseAmount * ($this->tax_rate / 100);
        $this->total = $baseAmount + $this->tax_amount;

        return $this;
    }
}
