<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomLine extends Model
{
    protected $table = 'bom_lines';

    protected $fillable = [
        'bom_id', 'line_number', 'product_id', 'uom_id',
        'quantity', 'quantity_formula', 'scrap_percentage',
        'is_alternative', 'alternative_for_id', 'cost_per_unit', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'scrap_percentage' => 'decimal:2',
            'cost_per_unit' => 'decimal:4',
            'is_alternative' => 'boolean',
        ];
    }

    public function bom(): BelongsTo { return $this->belongsTo(BillOfMaterial::class, 'bom_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class, 'uom_id'); }
    public function alternativeFor(): BelongsTo { return $this->belongsTo(BomLine::class, 'alternative_for_id'); }
    public function alternatives(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BomLine::class, 'alternative_for_id');
    }
}
