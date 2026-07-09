<?php

namespace App\Models;

use App\Enums\PickingType;
use App\Enums\PickingStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockPicking extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'picking_number', 'company_id', 'picking_type', 'status', 'origin',
        'source_location_id', 'destination_location_id',
        'vendor_id', 'customer_id',
        'scheduled_date', 'effective_date',
        'purchase_order_id', 'notes', 'internal_notes',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'picking_type' => PickingType::class,
            'status' => PickingStatus::class,
            'scheduled_date' => 'date',
            'effective_date' => 'date',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function sourceLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'source_location_id'); }
    public function destinationLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'destination_location_id'); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function moves(): HasMany { return $this->hasMany(StockMove::class); }

    public function scopeByType($query, PickingType $type) { return $query->where('picking_type', $type); }
    public function scopeByStatus($query, PickingStatus $status) { return $query->where('status', $status); }
    public function scopeIncoming($query) { return $query->where('picking_type', PickingType::Incoming); }
    public function scopeOutgoing($query) { return $query->where('picking_type', PickingType::Outgoing); }
    public function scopeInternal($query) { return $query->where('picking_type', PickingType::Internal); }

    public function canTransitionTo(PickingStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(PickingStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}"
            );
        }
        return $this->update(['status' => $newStatus]);
    }
}
