<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'warehouse_id', 'location_id', 'vendor_id',
        'name', 'min_qty', 'max_qty', 'qty_multiple',
        'lead_days', 'safety_stock_days', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'min_qty' => 'decimal:4',
            'max_qty' => 'decimal:4',
            'qty_multiple' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function needsReplenishment(float $currentQty): bool
    {
        return $currentQty <= $this->min_qty;
    }

    public function getReplenishQty(float $currentQty): float
    {
        if (!$this->needsReplenishment($currentQty)) {
            return 0;
        }
        $needed = $this->max_qty - $currentQty;
        return max($this->qty_multiple, ceil($needed / $this->qty_multiple) * $this->qty_multiple);
    }
}
