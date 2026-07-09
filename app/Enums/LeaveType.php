<?php

namespace App\Enums;

enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Personal = 'personal';
    case Maternity = 'maternity';
    case Paternity = 'paternity';
    case Unpaid = 'unpaid';
    case Marriage = 'marriage';
    case Bereavement = 'bereavement';
    case Religious = 'religious';

    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Annual Leave',
            self::Sick => 'Sick Leave',
            self::Personal => 'Personal Leave',
            self::Maternity => 'Maternity Leave',
            self::Paternity => 'Paternity Leave',
            self::Unpaid => 'Unpaid Leave',
            self::Marriage => 'Marriage Leave',
            self::Bereavement => 'Bereavement Leave',
            self::Religious => 'Religious Leave',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Annual => 'green',
            self::Sick => 'orange',
            self::Personal => 'blue',
            self::Maternity => 'pink',
            self::Paternity => 'indigo',
            self::Unpaid => 'gray',
            self::Marriage => 'purple',
            self::Bereavement => 'gray',
            self::Religious => 'yellow',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
