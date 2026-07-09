<?php

namespace App\Enums;

enum PickingStatus: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Confirmed = 'confirmed';
    case Assigned = 'assigned';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Waiting => 'Waiting',
            self::Confirmed => 'Confirmed',
            self::Assigned => 'Assigned',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Waiting => 'yellow',
            self::Confirmed => 'blue',
            self::Assigned => 'indigo',
            self::Done => 'green',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Waiting, self::Cancelled],
            self::Waiting => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Assigned, self::Cancelled],
            self::Assigned => [self::Done],
            self::Done => [],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
