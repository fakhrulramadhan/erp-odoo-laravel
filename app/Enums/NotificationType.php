<?php

namespace App\Enums;

enum NotificationType: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Success = 'success';
    case Error = 'error';

    public function icon(): string
    {
        return match ($this) {
            self::Info => 'info',
            self::Warning => 'alert-triangle',
            self::Success => 'check-circle',
            self::Error => 'x-circle',
        };
    }
}
