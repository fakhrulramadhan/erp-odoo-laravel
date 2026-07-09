<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UomConversion extends Model
{
    use HasFactory;

    protected $fillable = ['from_uom_id', 'to_uom_id', 'ratio'];

    protected $casts = [
        'ratio' => 'decimal:6',
    ];

    public function fromUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'from_uom_id');
    }

    public function toUom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'to_uom_id');
    }
}
