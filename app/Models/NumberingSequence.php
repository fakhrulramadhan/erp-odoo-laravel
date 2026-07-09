<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberingSequence extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'code', 'prefix', 'suffix',
        'next_number', 'padding', 'reset_yearly',
        'current_year', 'is_active',
    ];

    protected $casts = [
        'next_number' => 'integer',
        'padding' => 'integer',
        'reset_yearly' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Generate the next number in the sequence.
     */
    public function generateNext(): string
    {
        $year = date('Y');

        if ($this->reset_yearly && $this->current_year !== $year) {
            $this->update([
                'next_number' => 1,
                'current_year' => $year,
            ]);
        }

        $number = str_pad($this->next_number, $this->padding, '0', STR_PAD_LEFT);
        $this->increment('next_number');

        $parts = array_filter([
            $this->prefix,
            $number,
            $this->suffix,
        ]);

        return implode('', $parts);
    }
}
