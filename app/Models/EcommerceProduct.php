<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EcommerceProduct extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'product_id', 'slug', 'short_description', 'long_description',
        'meta_title', 'meta_description', 'is_featured', 'is_visible',
        'compare_price', 'stock_quantity', 'weight', 'attributes', 'sequence',
        'company_id', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_visible' => 'boolean',
        'compare_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'weight' => 'decimal:2',
        'attributes' => 'array',
        'sequence' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(EcommerceProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(EcommerceProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
