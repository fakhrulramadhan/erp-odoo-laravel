<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosPayment extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'pos_order_id', 'payment_method_id', 'payment_type',
        'amount', 'reference', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────
    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
