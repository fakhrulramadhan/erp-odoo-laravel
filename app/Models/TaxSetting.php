<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSetting extends Model
{
    use SoftDeletes;

    protected $table = 'tax_settings';

    protected $fillable = [
        'company_id', 'name', 'code', 'rate', 'type',
        'is_inclusive', 'description', 'is_active',
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
}
