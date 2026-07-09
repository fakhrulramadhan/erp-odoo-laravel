<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAdjustment extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'adjustment_number', 'company_id', 'warehouse_id',
        'status', 'adjustment_date', 'reason', 'notes',
        'validated_by', 'validated_at',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'adjustment_date' => 'date',
            'validated_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function validator(): BelongsTo { return $this->belongsTo(User::class, 'validated_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function lines(): HasMany { return $this->hasMany(InventoryAdjustmentLine::class); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
}
