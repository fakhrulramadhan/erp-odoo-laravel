<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';
    case Renewed = 'renewed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Expired => 'Expired',
            self::Terminated => 'Terminated',
            self::Renewed => 'Renewed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::Expired => 'orange',
            self::Terminated => 'red',
            self::Renewed => 'blue',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active, self::Terminated],
            self::Active => [self::Expired, self::Terminated, self::Renewed],
            self::Expired => [self::Renewed],
            self::Terminated => [],
            self::Renewed => [self::Active],
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
