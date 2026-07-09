<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'code', 'barcode', 'name', 'description', 'category_id', 'uom_id',
        'type', 'purchase_price', 'sales_price', 'tax_id', 'minimum_stock',
        'image', 'weight', 'volume',
        'is_active', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sales_price' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(TaxSetting::class, 'tax_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeStockable($query)
    {
        return $query->where('type', 'stockable');
    }
}
