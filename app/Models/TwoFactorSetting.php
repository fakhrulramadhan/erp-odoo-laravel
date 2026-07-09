<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorSetting extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'user_id', 'is_enabled', 'method', 'secret',
        'recovery_codes', 'verified_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'recovery_codes' => 'array',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
