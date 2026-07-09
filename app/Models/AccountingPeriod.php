<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingPeriod extends Model
{
    use SoftDeletes;

    protected $table = 'accounting_periods';

    protected $fillable = ['fiscal_year_id', 'name', 'code', 'start_date', 'end_date', 'is_closed'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_closed' => 'boolean',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }
}
