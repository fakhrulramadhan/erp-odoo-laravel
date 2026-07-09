<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityCheckLine extends Model
{
    protected $fillable = [
        'quality_check_id', 'line_number', 'check_item',
        'description', 'result', 'notes', 'photo_path',
    ];

    public function qualityCheck(): BelongsTo { return $this->belongsTo(QualityCheck::class); }
}
