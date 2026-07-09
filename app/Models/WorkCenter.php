<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCenter extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'code', 'name', 'description',
        'warehouse_id', 'location_id',
        'capacity', 'working_hours_per_day', 'cost_per_hour',
        'efficiency', 'setup_time_minutes', 'cleanup_time_minutes',
        'is_active', 'notes',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'cost_per_hour' => 'decimal:2',
            'efficiency' => 'decimal:2',
            'working_hours_per_day' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function location(): BelongsTo { return $this->belongsTo(StockLocation::class, 'location_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function equipment(): HasMany { return $this->hasMany(Equipment::class); }
    public function routingOperations(): HasMany { return $this->hasMany(RoutingOperation::class); }

    public function operators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'work_center_operators')
            ->withPivot('role', 'is_active')
            ->withTimestamps();
    }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeByCompany($query, int $companyId) { return $query->where('company_id', $companyId); }
}
