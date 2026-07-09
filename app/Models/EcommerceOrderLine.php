<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceOrderLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_order_id', 'ecommerce_product_id', 'variant_id',
        'product_name', 'quantity', 'unit_price',
        'discount_amount', 'tax_amount', 'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────
    public function ecommerceOrder(): BelongsTo
    {
        return $this->belongsTo(EcommerceOrder::class);
    }

    public function ecommerceProduct(): BelongsTo
    {
        return $this->belongsTo(EcommerceProduct::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(EcommerceProductVariant::class);
    }
}
