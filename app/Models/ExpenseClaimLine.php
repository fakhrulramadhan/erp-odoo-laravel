<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseClaimLine extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'expense_claim_id', 'category', 'description',
        'amount', 'receipt_path', 'expense_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function expenseClaim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class);
    }
}
