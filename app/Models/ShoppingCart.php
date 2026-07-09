<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShoppingCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_token', 'customer_id', 'company_id', 'total', 'item_count',
        'last_activity_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'item_count' => 'integer',
        'last_activity_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
