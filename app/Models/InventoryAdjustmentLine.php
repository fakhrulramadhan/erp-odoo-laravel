<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustmentLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_adjustment_id', 'product_id', 'location_id', 'lot_id',
        'theoretical_qty', 'actual_qty', 'difference', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'theoretical_qty' => 'decimal:4',
            'actual_qty' => 'decimal:4',
            'difference' => 'decimal:4',
        ];
    }

    public function inventoryAdjustment(): BelongsTo { return $this->belongsTo(InventoryAdjustment::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class); }
    public function lot(): BelongsTo { return $this->belongsTo(Lot::class); }
}
