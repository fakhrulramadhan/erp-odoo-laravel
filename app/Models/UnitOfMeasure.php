<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnitOfMeasure extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'unit_of_measures';

    protected $fillable = [
        'code', 'name', 'type', 'description',
        'is_active', 'created_by', 'updated_by', 'deleted_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'uom_id');
    }

    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'from_uom_id');
    }

    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'to_uom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ── Scopes ─────────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
