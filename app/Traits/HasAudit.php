<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Model;

trait HasAudit
{
    public static function bootHasAudit(): void
    {
        static::created(function (Model $model) {
            $model->logAudit('created');
        });

        static::updated(function (Model $model) {
            $dirty = $model->getDirty();
            if (!empty($dirty)) {
                $old = [];
                $new = [];
                foreach ($dirty as $key => $value) {
                    if (in_array($key, ['updated_at', 'created_at'])) continue;
                    $old[$key] = $model->getOriginal($key);
                    $new[$key] = $value;
                }
                if (!empty($new)) {
                    $model->logAudit('updated', $old, $new);
                }
            }
        });

        static::deleted(function (Model $model) {
            $model->logAudit('deleted');
        });
    }

    public function logAudit(
        string $event,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        AuditTrail::create([
            'user_id' => auth()->id(),
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => request()?->fullUrl(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public function auditTrails()
    {
        return $this->morphMany(AuditTrail::class, 'auditable');
    }
}
