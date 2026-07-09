<?php

namespace App\Models;

use App\Enums\QualityCheckStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityCheck extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'check_number', 'inspection_type',
        'quality_control_point_id',
        'source_type', 'source_id',
        'product_id', 'quantity', 'uom_id',
        'status', 'result',
        'inspector_id', 'inspection_date',
        'checklist_result', 'notes',
        'defect_type', 'corrective_action',
        'rejected_qty', 'quarantine_location_id',
        'approved_by', 'approved_at',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => QualityCheckStatus::class,
            'quantity' => 'decimal:4',
            'rejected_qty' => 'decimal:4',
            'inspection_date' => 'datetime',
            'approved_at' => 'datetime',
            'checklist_result' => 'array',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function qualityControlPoint(): BelongsTo { return $this->belongsTo(QualityControlPoint::class); }
    public function source(): MorphTo { return $this->morphTo(); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function uom(): BelongsTo { return $this->belongsTo(UnitOfMeasure::class, 'uom_id'); }
    public function inspector(): BelongsTo { return $this->belongsTo(User::class, 'inspector_id'); }
    public function quarantineLocation(): BelongsTo { return $this->belongsTo(StockLocation::class, 'quarantine_location_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function lines(): HasMany { return $this->hasMany(QualityCheckLine::class)->orderBy('line_number'); }

    public function scopeByStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeByType($query, string $type) { return $query->where('inspection_type', $type); }

    public function canTransitionTo(QualityCheckStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    public function transitionTo(QualityCheckStatus $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            throw new \DomainException("Cannot transition from {$this->status->value} to {$newStatus->value}");
        }
        return $this->update(['status' => $newStatus]);
    }
}
