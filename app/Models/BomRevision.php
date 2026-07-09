<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomRevision extends Model
{
    protected $table = 'bom_revisions';

    protected $fillable = [
        'bom_id', 'revision_number', 'revision_code',
        'description', 'changes', 'approved_by', 'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function bom(): BelongsTo { return $this->belongsTo(BillOfMaterial::class, 'bom_id'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
