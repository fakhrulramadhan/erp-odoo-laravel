<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use SoftDeletes;

    protected $table = 'taxes';

    protected $fillable = [
        'company_id', 'name', 'code', 'rate', 'type', 'is_inclusive', 'is_active', 'description'
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_inclusive' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
