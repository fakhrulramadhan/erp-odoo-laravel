<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentShare extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'document_id', 'shareable_type', 'shareable_id',
        'permission', 'shared_by', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }
}
