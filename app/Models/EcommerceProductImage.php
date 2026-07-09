<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EcommerceProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ecommerce_product_id', 'image_path', 'alt_text', 'sequence', 'is_primary',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'is_primary' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function ecommerceProduct(): BelongsTo
    {
        return $this->belongsTo(EcommerceProduct::class);
    }
}
