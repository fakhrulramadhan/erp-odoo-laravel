<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceOrder extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'order_number', 'maintenance_type',
        'equipment_id', 'status', 'priority',
        'description', 'checklist',
        'scheduled_date', 'due_date',
        'started_at', 'completed_at',
        'downtime_minutes',
        'cost', 'parts_cost', 'labor_cost',
        'assigned_to', 'vendor_id',
        'notes', 'resolution',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'scheduled_date' => 'date',
            'due_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cost' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'labor_cost' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function equipment(): BelongsTo { return $this->belongsTo(Equipment::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeByType($query, string $type) { return $query->where('maintenance_type', $type); }
    public function scopeByPriority($query, string $priority) { return $query->where('priority', $priority); }
    public function scopeScheduled($query) { return $query->whereNotNull('scheduled_date'); }
    public function scopeOverdue($query) { return $query->where('due_date', '<', now())->whereNotIn('status', ['completed', 'cancelled']); }
}
