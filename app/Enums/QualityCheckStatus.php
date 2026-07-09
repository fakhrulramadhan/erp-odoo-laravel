<?php

namespace App\Enums;

enum QualityCheckStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Passed = 'passed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Passed => 'Passed',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::InProgress => 'yellow',
            self::Passed => 'green',
            self::Failed => 'red',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::InProgress, self::Cancelled],
            self::InProgress => [self::Passed, self::Failed],
            self::Passed => [],
            self::Failed => [],
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
