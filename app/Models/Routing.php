<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Routing extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'routings';

    protected $fillable = [
        'company_id', 'routing_number', 'name', 'description',
        'product_id', 'is_active',
        'created_by', 'updated_by', 'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function operations(): HasMany { return $this->hasMany(RoutingOperation::class, 'routing_id')->orderBy('sequence'); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeByCompany($query, int $companyId) { return $query->where('company_id', $companyId); }
}
