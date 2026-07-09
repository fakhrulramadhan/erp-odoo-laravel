<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'scrap_number', 'scrap_type',
        'source_type', 'source_id',
        'product_id', 'warehouse_id', 'location_id',
        'quantity', 'uom_id', 'unit_cost', 'total_cost',
        'status', 'reason', 'notes', 'lot_id',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function source(): MorphTo { return $this->morphTo(); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class, 'location_id'); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class, 'uom_id'); }
    public function lot(): BelongsTo { return $this->belongsTo(Lot::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeByType($query, string $type) { return $query->where('scrap_type', $type); }
}
