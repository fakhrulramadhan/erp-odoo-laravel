<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManufacturingOrderLine extends Model
{
    protected $fillable = [
        'manufacturing_order_id', 'line_number', 'product_id', 'uom_id',
        'quantity', 'reserved_qty', 'consumed_qty', 'scrap_qty',
        'line_type', 'unit_cost', 'total_cost',
        'source_location_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'reserved_qty' => 'decimal:4',
            'consumed_qty' => 'decimal:4',
            'scrap_qty' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
        ];
    }

    public function manufacturingOrder(): BelongsTo { return $this->belongsTo(ManufacturingOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class, 'uom_id'); }
    public function sourceLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'source_location_id'); }
}
