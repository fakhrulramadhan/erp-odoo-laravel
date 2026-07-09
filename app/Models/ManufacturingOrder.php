<?php

namespace App\Models;

use App\Enums\ManufacturingOrderStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManufacturingOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'branch_id', 'order_number',
        'product_id', 'bom_id', 'routing_id',
        'warehouse_id', 'source_location_id', 'destination_location_id',
        'status', 'quantity', 'produced_qty', 'scrap_qty', 'uom_id',
        'planned_date', 'planned_start', 'planned_finish',
        'actual_start', 'actual_finish',
        'material_cost', 'labor_cost', 'machine_cost', 'overhead_cost',
        'total_cost', 'unit_cost',
        'notes', 'production_notes',
        'approved_by', 'approved_at', 'origin',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => ManufacturingOrderStatus::class,
            'quantity' => 'decimal:4',
            'produced_qty' => 'decimal:4',
            'scrap_qty' => 'decimal:4',
            'material_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
            'machine_cost' => 'decimal:2',
            'overhead_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'unit_cost' => 'decimal:4',
            'planned_date' => 'date',
            'planned_start' => 'date',
            'planned_finish' => 'date',
            'actual_start' => 'datetime',
            'actual_finish' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function bom(): BelongsTo { return $this->belongsTo(BillOfMaterial::class, 'bom_id'); }
    public function routing(): BelongsTo { return $this->belongsTo(Routing::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function sourceLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'source_location_id'); }
    public function destinationLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'destination_location_id'); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class, 'uom_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function lines(): HasMany { return $this->hasMany(ManufacturingOrderLine::class)->orderBy('line_number'); }
    public function workOrders(): HasMany { return $this->hasMany(WorkOrder::class)->orderBy('sequence'); }
    public function productionCosts(): HasMany { return $this->hasMany(ProductionCost::class); }
    public function qualityChecks(): MorphMany { return $this->morphMany(QualityCheck::class, 'source'); }
    public function scrapOrders(): MorphMany { return $this->morphMany(ScrapOrder::class, 'source'); }

    // ── Scopes ─────────────────────────────────────
    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeDraft($query) { return $query->where('status', ManufacturingOrderStatus::Draft); }
    public function scopeInProduction($query) { return $query->where('status', ManufacturingOrderStatus::InProduction); }
    public function scopeByCompany($query, int $companyId) { return $query->where('company_id', $companyId); }
    public function scopeByProduct($query, int $productId) { return $query->where('product_id', $productId); }

    // ── State Machine ──────────────────────────────
    public function canTransitionTo(ManufacturingOrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(ManufacturingOrderStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException(
                "Cannot transition from {$this->status->value} to {$newStatus->value}"
            );
        }
        return $this->update(['status' => $newStatus]);
    }

    // ── Calculations ───────────────────────────────
    public function recalculate(): void
    {
        $this->load('productionCosts');
        $costs = $this->productionCosts;

        $materialCost = (float) $costs->where('cost_type', 'material')->sum('total_cost');
        $laborCost = (float) $costs->where('cost_type', 'labor')->sum('total_cost');
        $machineCost = (float) $costs->where('cost_type', 'machine')->sum('total_cost');
        $overheadCost = (float) $costs->where('cost_type', 'overhead')->sum('total_cost');
        $totalCost = $materialCost + $laborCost + $machineCost + $overheadCost;

        $this->update([
            'material_cost' => $materialCost,
            'labor_cost' => $laborCost,
            'machine_cost' => $machineCost,
            'overhead_cost' => $overheadCost,
            'total_cost' => $totalCost,
            'unit_cost' => $this->produced_qty > 0 ? round($totalCost / (float) $this->produced_qty, 4) : 0,
        ]);
    }
}
