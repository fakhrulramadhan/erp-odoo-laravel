<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'branch_id', 'asset_number', 'name', 'description',
        'category_id', 'status', 'condition',
        'product_id', 'warehouse_id', 'location_id',
        'assigned_to', 'department_id',
        'serial_number', 'manufacturer', 'model_number',
        'purchase_date', 'commission_date',
        'purchase_value', 'current_value', 'residual_value',
        'accumulated_depreciation',
        'depreciation_method', 'useful_life_months',
        'warranty_start', 'warranty_end', 'warranty_provider',
        'vendor_id',
        'disposal_date', 'disposal_value', 'disposal_reason',
        'notes',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssetStatus::class,
            'purchase_date' => 'date',
            'commission_date' => 'date',
            'purchase_value' => 'decimal:2',
            'current_value' => 'decimal:2',
            'residual_value' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'warranty_start' => 'date',
            'warranty_end' => 'date',
            'disposal_date' => 'date',
            'disposal_value' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function category(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class, 'location_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function depreciations(): HasMany { return $this->hasMany(AssetDepreciation::class)->orderByDesc('depreciation_date'); }
    public function transfers(): HasMany { return $this->hasMany(AssetTransfer::class)->orderByDesc('transfer_date'); }
    public function equipment(): HasMany { return $this->hasMany(Equipment::class); }

    public function scopeByStatus($query, AssetStatus $status) { return $query->where('status', $status); }
    public function scopeActive($query) { return $query->where('status', AssetStatus::Active); }
    public function scopeByCategory($query, int $categoryId) { return $query->where('category_id', $categoryId); }
    public function scopeByCompany($query, int $companyId) { return $query->where('company_id', $companyId); }

    public function canTransitionTo(AssetStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(AssetStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException("Cannot transition from {$this->status->value} to {$newStatus->value}");
        }
        return $this->update(['status' => $newStatus]);
    }

    public function getMonthlyDepreciation(): float
    {
        if ($this->useful_life_months <= 0) return 0;
        $depreciableValue = (float) $this->purchase_value - (float) $this->residual_value;
        return round($depreciableValue / $this->useful_life_months, 2);
    }

    public function getBookValue(): float
    {
        return round((float) $this->purchase_value - (float) $this->accumulated_depreciation, 2);
    }
}
