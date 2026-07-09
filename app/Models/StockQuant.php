<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockQuant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'location_id', 'lot_id',
        'quantity', 'reserved_quantity', 'available_quantity',
        'incoming_quantity', 'outgoing_quantity',
        'unit_cost', 'total_value', 'last_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'reserved_quantity' => 'decimal:4',
            'available_quantity' => 'decimal:4',
            'incoming_quantity' => 'decimal:4',
            'outgoing_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_value' => 'decimal:2',
            'last_updated_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class); }
    public function lot(): BelongsTo { return $this->belongsTo(Lot::class); }

    public function scopeByProduct($query, int $productId) { return $query->where('product_id', $productId); }
    public function scopeByLocation($query, int $locationId) { return $query->where('location_id', $locationId); }
    public function scopePositive($query) { return $query->where('quantity', '>', 0); }
    public function scopeNegative($query) { return $query->where('quantity', '<', 0); }
    public function scopeLowStock($query) {
        return $query->whereColumn('quantity', '<', 'reserved_quantity')
            ->orWhere(fn($q) => $q->where('quantity', '<=', 0)->where('reserved_quantity', '>', 0));
    }

    public function recalculate(): void
    {
        $this->available_quantity = $this->quantity - $this->reserved_quantity;
        $this->total_value = $this->quantity * $this->unit_cost;
        $this->last_updated_at = now();
    }

    public function addQuantity(float $qty, ?float $cost = null): void
    {
        $this->quantity += $qty;
        if ($cost !== null) {
            $this->unit_cost = $cost;
        }
        $this->recalculate();
        $this->save();
    }

    public function removeQuantity(float $qty): void
    {
        $this->quantity -= $qty;
        $this->recalculate();
        $this->save();
    }
}
