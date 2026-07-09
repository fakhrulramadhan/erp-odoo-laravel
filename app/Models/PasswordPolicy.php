<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordPolicy extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'min_length', 'require_uppercase', 'require_lowercase',
        'require_number', 'require_special_char', 'max_age_days',
        'history_count', 'max_login_attempts', 'lockout_minutes',
        'company_id',
    ];

    protected $casts = [
        'min_length' => 'integer',
        'require_uppercase' => 'boolean',
        'require_lowercase' => 'boolean',
        'require_number' => 'boolean',
        'require_special_char' => 'boolean',
        'max_age_days' => 'integer',
        'history_count' => 'integer',
        'max_login_attempts' => 'integer',
        'lockout_minutes' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
