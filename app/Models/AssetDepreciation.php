<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDepreciation extends Model
{
    protected $table = 'asset_depreciations';

    protected $fillable = [
        'asset_id', 'journal_entry_id', 'depreciation_date',
        'period_number', 'depreciation_amount',
        'accumulated_depreciation', 'book_value',
        'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'depreciation_date' => 'date',
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'book_value' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
