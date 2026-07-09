<?php

namespace App\Models;

use App\Enums\BomStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfMaterial extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'bill_of_materials';

    protected $fillable = [
        'company_id', 'bom_number', 'product_id', 'uom_id',
        'version', 'revision', 'bom_type', 'status', 'quantity',
        'scrap_percentage', 'routing_id', 'work_center_id',
        'effective_date', 'expiry_date', 'is_default', 'notes',
        'approved_by', 'approved_at',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => BomStatus::class,
            'quantity' => 'decimal:4',
            'scrap_percentage' => 'decimal:2',
            'effective_date' => 'date',
            'expiry_date' => 'date',
            'approved_at' => 'datetime',
            'is_default' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class, 'uom_id'); }
    public function workCenter(): BelongsTo { return $this->belongsTo(WorkCenter::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function lines(): HasMany { return $this->hasMany(BomLine::class, 'bom_id')->orderBy('line_number'); }
    public function revisions(): HasMany { return $this->hasMany(BomRevision::class, 'bom_id')->orderByDesc('revision_number'); }
    public function manufacturingOrders(): HasMany { return $this->hasMany(ManufacturingOrder::class, 'bom_id'); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeActive($query) { return $query->where('status', BomStatus::Active); }
    public function scopeDefault($query) { return $query->where('is_default', true); }
    public function scopeByProduct($query, int $productId) { return $query->where('product_id', $productId); }

    public function canTransitionTo(BomStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(BomStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException("Cannot transition from {$this->status->value} to {$newStatus->value}");
        }
        return $this->update(['status' => $newStatus]);
    }

    public function recalculate(): void
    {
        $this->load('lines');
        $totalCost = 0;
        foreach ($this->lines as $line) {
            $line->update(['cost_per_unit' => $line->product?->standard_cost ?? 0]);
            $totalCost += $line->cost_per_unit * $line->quantity;
        }
    }

    public function getEstimatedCost(): float
    {
        $this->load('lines');
        return (float) $this->lines->sum(fn($line) => $line->cost_per_unit * $line->quantity);
    }
}
