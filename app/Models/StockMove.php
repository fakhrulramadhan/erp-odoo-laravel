<?php

namespace App\Models;

use App\Enums\StockMoveStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMove extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_picking_id', 'product_id', 'uom_id', 'lot_id',
        'move_number', 'status', 'origin',
        'source_location_id', 'destination_location_id',
        'quantity', 'reserved_qty', 'done_qty',
        'unit_cost', 'total_cost',
        'scheduled_date', 'effective_date', 'notes',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => StockMoveStatus::class,
            'quantity' => 'decimal:4',
            'reserved_qty' => 'decimal:4',
            'done_qty' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
            'scheduled_date' => 'date',
            'effective_date' => 'date',
        ];
    }

    public function stockPicking(): BelongsTo { return $this->belongsTo(StockPicking::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class); }
    public function lot(): BelongsTo { return $this->belongsTo(Lot::class); }
    public function sourceLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'source_location_id'); }
    public function destinationLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'destination_location_id'); }

    public function scopeByStatus($query, StockMoveStatus $status) { return $query->where('status', $status); }
    public function scopeByProduct($query, int $productId) { return $query->where('product_id', $productId); }

    public function getRemainingQty(): float
    {
        return max(0, (float) ($this->quantity - $this->done_qty));
    }
}
