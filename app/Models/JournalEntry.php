<?php

namespace App\Models;

use App\Enums\FinanceStatus;
use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes, HasAudit;

    protected $table = 'journal_entries';

    protected $fillable = [
        'company_id', 'journal_id', 'fiscal_year_id', 'accounting_period_id', 'entry_number', 'reference',
        'entry_date', 'description', 'status', 'reversed_entry_id', 'created_by', 'updated_by', 'posted_by',
        'posted_at', 'source_type', 'source_id'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'status' => FinanceStatus::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function accountingPeriod(): BelongsTo
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
