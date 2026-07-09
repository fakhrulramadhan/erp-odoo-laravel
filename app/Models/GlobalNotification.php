<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GlobalNotification extends Model
{
    protected $table = 'global_notifications';

    protected $fillable = [
        'company_id', 'type', 'title', 'message',
        'link', 'is_broadcast',
    ];

    protected $casts = [
        'is_broadcast' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function markAsRead(User $user): void
    {
        $this->users()->updateExistingPivot($user->id, [
            'read_at' => now(),
        ]);
    }
}
