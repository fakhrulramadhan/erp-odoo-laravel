<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id', 'product_id', 'uom_id', 'tax_id',
        'line_number', 'description',
        'quantity', 'delivered_qty', 'unit_price',
        'discount_percent', 'tax_rate',
        'subtotal', 'discount_amount', 'tax_amount', 'total',
        'delivery_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'delivered_qty' => 'decimal:4',
            'unit_price' => 'decimal:4',
            'discount_percent' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_date' => 'date',
        ];
    }

    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class); }
    public function tax(): BelongsTo { return $this->belongsTo(TaxSetting::class, 'tax_id'); }

    public function calculateTotals(): static
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        $this->discount_amount = $this->subtotal * ($this->discount_percent / 100);
        $base = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $base * ($this->tax_rate / 100);
        $this->total = $base + $this->tax_amount;

        return $this;
    }

    public function getRemainingQty(): float
    {
        return max(0, (float) $this->quantity - (float) $this->delivered_qty);
    }
}
