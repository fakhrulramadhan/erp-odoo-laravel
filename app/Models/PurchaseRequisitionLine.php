<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisitionLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_requisition_id', 'product_id', 'uom_id',
        'line_number', 'description', 'quantity', 'estimated_price', 'required_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'estimated_price' => 'decimal:4',
            'required_date' => 'date',
        ];
    }

    public function purchaseRequisition(): BelongsTo { return $this->belongsTo(PurchaseRequisition::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class); }
}
