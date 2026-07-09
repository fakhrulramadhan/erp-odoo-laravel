<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityControlPoint extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'company_id', 'code', 'name', 'inspection_type',
        'product_id', 'work_center_id', 'is_active',
        'checklist', 'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function workCenter(): BelongsTo { return $this->belongsTo(WorkCenter::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeByType($query, string $type) { return $query->where('inspection_type', $type); }
}
