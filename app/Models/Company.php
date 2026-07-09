<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'legal_name', 'email', 'phone', 'website',
        'address', 'city', 'state', 'zip_code', 'country',
        'tax_id', 'logo', 'favicon', 'currency_id', 'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function taxSettings(): HasMany
    {
        return $this->hasMany(TaxSetting::class);
    }

    public function numberingSequences(): HasMany
    {
        return $this->hasMany(NumberingSequence::class);
    }
}
