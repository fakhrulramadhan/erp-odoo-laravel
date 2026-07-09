<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionCost extends Model
{
    protected $fillable = [
        'manufacturing_order_id', 'cost_type', 'description',
        'quantity', 'unit_cost', 'total_cost',
        'product_id', 'work_center_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
        ];
    }

    public function manufacturingOrder(): BelongsTo { return $this->belongsTo(ManufacturingOrder::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function workCenter(): BelongsTo { return $this->belongsTo(WorkCenter::class); }
}
