<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceProductVariant extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'ecommerce_product_id', 'sku', 'name', 'attributes',
        'price', 'compare_price', 'stock_quantity', 'is_active', 'sequence',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'sequence' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function ecommerceProduct(): BelongsTo
    {
        return $this->belongsTo(EcommerceProduct::class);
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
