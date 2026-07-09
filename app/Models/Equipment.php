<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'equipment';

    protected $fillable = [
        'company_id', 'code', 'name', 'type', 'description',
        'work_center_id', 'warehouse_id', 'location_id', 'asset_id',
        'serial_number', 'manufacturer', 'model',
        'purchase_date', 'warranty_expiry', 'status',
        'capacity', 'capacity_uom',
        'last_maintenance_days', 'next_maintenance_date',
        'is_active', 'notes',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'next_maintenance_date' => 'date',
            'capacity' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function workCenter(): BelongsTo { return $this->belongsTo(WorkCenter::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class, 'location_id'); }
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function maintenanceOrders(): HasMany { return $this->hasMany(MaintenanceOrder::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeByType($query, string $type) { return $query->where('type', $type); }
    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
}
