<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'manufacturing_order_id', 'work_order_number', 'work_center_id',
        'sequence', 'name', 'description', 'status',
        'duration_minutes', 'actual_duration_minutes',
        'started_at', 'finished_at', 'cost',
        'operator_id', 'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'cost' => 'decimal:2',
        ];
    }

    public function manufacturingOrder(): BelongsTo { return $this->belongsTo(ManufacturingOrder::class); }
    public function workCenter(): BelongsTo { return $this->belongsTo(WorkCenter::class); }
    public function operator(): BelongsTo { return $this->belongsTo(User::class, 'operator_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
