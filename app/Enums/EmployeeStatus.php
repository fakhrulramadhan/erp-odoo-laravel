<?php

namespace App\Enums;

enum EmployeeStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case Resigned = 'resigned';
    case Terminated = 'terminated';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::OnLeave => 'On Leave',
            self::Suspended => 'Suspended',
            self::Resigned => 'Resigned',
            self::Terminated => 'Terminated',
            self::Retired => 'Retired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::OnLeave => 'yellow',
            self::Suspended => 'orange',
            self::Resigned => 'red',
            self::Terminated => 'red',
            self::Retired => 'purple',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
