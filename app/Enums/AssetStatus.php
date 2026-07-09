<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case InMaintenance = 'in_maintenance';
    case Transferred = 'transferred';
    case Disposed = 'disposed';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::InMaintenance => 'In Maintenance',
            self::Transferred => 'Transferred',
            self::Disposed => 'Disposed',
            self::Retired => 'Retired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::InMaintenance => 'yellow',
            self::Transferred => 'blue',
            self::Disposed => 'red',
            self::Retired => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active],
            self::Active => [self::InMaintenance, self::Transferred, self::Disposed, self::Retired],
            self::InMaintenance => [self::Active, self::Disposed],
            self::Transferred => [self::Active],
            self::Disposed => [],
            self::Retired => [],
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
