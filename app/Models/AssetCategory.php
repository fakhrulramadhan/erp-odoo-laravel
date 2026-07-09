<?php

namespace App\Models;

use App\Enums\AssetStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'asset_categories';

    protected $fillable = [
        'company_id', 'code', 'name', 'description', 'parent_id',
        'depreciation_expense_account_id', 'accumulated_depreciation_account_id',
        'asset_account_id', 'disposal_account_id',
        'depreciation_method', 'useful_life_months', 'residual_value_rate',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'residual_value_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function parent(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(AssetCategory::class, 'parent_id'); }
    public function depreciationExpenseAccount(): BelongsTo { return $this->belongsTo(Account::class, 'depreciation_expense_account_id'); }
    public function accumulatedDepreciationAccount(): BelongsTo { return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id'); }
    public function assetAccount(): BelongsTo { return $this->belongsTo(Account::class, 'asset_account_id'); }
    public function disposalAccount(): BelongsTo { return $this->belongsTo(Account::class, 'disposal_account_id'); }
    public function assets(): HasMany { return $this->hasMany(Asset::class, 'category_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeRoots($query) { return $query->whereNull('parent_id'); }
}
