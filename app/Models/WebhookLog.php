<?php

namespace App\Models;

use App\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasFactory, HasAudit;

    protected $fillable = [
        'webhook_id', 'event', 'payload', 'response_status',
        'response_body', 'status', 'attempts', 'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_status' => 'integer',
        'attempts' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
