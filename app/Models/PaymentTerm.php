<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTerm extends Model
{
    use SoftDeletes;

    protected $table = 'payment_terms';

    protected $fillable = ['company_id', 'name', 'code', 'days', 'description', 'is_active'];

    protected $casts = [
        'days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
