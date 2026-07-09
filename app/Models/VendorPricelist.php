<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorPricelist extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'product_id', 'uom_id', 'currency_id',
        'price', 'min_quantity', 'lead_days',
        'valid_from', 'valid_to', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:4',
            'min_quantity' => 'decimal:4',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class); }
    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeValid($query) {
        return $query->where(fn($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
            ->where(fn($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', now()));
    }
}
