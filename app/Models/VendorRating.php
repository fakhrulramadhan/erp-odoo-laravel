<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'purchase_order_id',
        'quality_rating', 'delivery_rating', 'price_rating', 'service_rating',
        'overall_rating', 'notes', 'rating_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quality_rating' => 'decimal:1',
            'delivery_rating' => 'decimal:1',
            'price_rating' => 'decimal:1',
            'service_rating' => 'decimal:1',
            'overall_rating' => 'decimal:1',
            'rating_date' => 'date',
        ];
    }

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
