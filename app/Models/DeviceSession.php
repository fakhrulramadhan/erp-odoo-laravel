<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSession extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'user_id', 'device', 'ip_address', 'user_agent',
        'token_hash', 'last_active_at', 'is_current',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
