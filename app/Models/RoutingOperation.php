<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingOperation extends Model
{
    protected $table = 'routing_operations';

    protected $fillable = [
        'routing_id', 'sequence', 'name', 'description',
        'work_center_id', 'duration_minutes', 'setup_time_minutes',
        'expected_output', 'notes',
    ];

    public function routing(): BelongsTo { return $this->belongsTo(Routing::class, 'routing_id'); }
    public function workCenter(): BelongsTo { return $this->belongsTo(WorkCenter::class); }
}
