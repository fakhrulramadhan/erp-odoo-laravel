<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lot extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $fillable = [
        'lot_number', 'product_id', 'company_id',
        'tracking_type', 'expiration_date', 'manufacture_date',
        'notes', 'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'manufacture_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }

    public function scopeActive($query) { return $query->where('is_active', true); }
    public function scopeExpired($query) { return $query->where('expiration_date', '<', now()); }
    public function scopeNotExpired($query) { return $query->where(fn($q) => $q->whereNull('expiration_date')->orWhere('expiration_date', '>=', now())); }
    public function scopeLot($query) { return $query->where('tracking_type', 'lot'); }
    public function scopeSerial($query) { return $query->where('tracking_type', 'serial'); }
}
