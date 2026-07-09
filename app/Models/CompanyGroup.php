<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyGroup extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'name', 'code', 'description', 'parent_id',
        'created_by', 'updated_by', 'deleted_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CompanyGroup::class, 'parent_id');
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
