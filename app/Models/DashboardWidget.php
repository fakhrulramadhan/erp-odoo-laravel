<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DashboardWidget extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'dashboard_id', 'name', 'type', 'chart_type',
        'data_source', 'config', 'layout', 'sequence',
        'is_active', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'config' => 'array',
        'layout' => 'array',
        'sequence' => 'integer',
        'is_active' => 'boolean',
    ];

    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
