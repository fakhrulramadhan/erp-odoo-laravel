<?php

namespace App\Enums;

enum ActivityType: string
{
    case Call = 'call';
    case Email = 'email';
    case Meeting = 'meeting';
    case Task = 'task';
    case Note = 'note';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Call => 'Call',
            self::Email => 'Email',
            self::Meeting => 'Meeting',
            self::Task => 'Task',
            self::Note => 'Note',
            self::Sms => 'SMS',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
