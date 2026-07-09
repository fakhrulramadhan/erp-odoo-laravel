<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Proposition = 'proposition';
    case Won = 'won';
    case Lost = 'lost';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Proposition => 'Proposition',
            self::Won => 'Won',
            self::Lost => 'Lost',
            self::Reopened => 'Reopened',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::Contacted => 'indigo',
            self::Qualified => 'purple',
            self::Proposition => 'orange',
            self::Won => 'green',
            self::Lost => 'red',
            self::Reopened => 'yellow',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Contacted, self::Lost],
            self::Contacted => [self::Qualified, self::Lost],
            self::Qualified => [self::Proposition, self::Lost],
            self::Proposition => [self::Won, self::Lost],
            self::Won => [self::Reopened],
            self::Lost => [self::Reopened],
            self::Reopened => [self::Contacted, self::Lost],
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
