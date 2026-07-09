<?php

namespace App\Enums;

enum OpportunityStage: string
{
    case New = 'new';
    case Qualification = 'qualification';
    case Proposition = 'proposition';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Qualification => 'Qualification',
            self::Proposition => 'Proposition',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::Qualification => 'indigo',
            self::Proposition => 'purple',
            self::Negotiation => 'orange',
            self::Won => 'green',
            self::Lost => 'red',
        };
    }

    public function probability(): int
    {
        return match ($this) {
            self::New => 10,
            self::Qualification => 25,
            self::Proposition => 50,
            self::Negotiation => 75,
            self::Won => 100,
            self::Lost => 0,
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Qualification, self::Lost],
            self::Qualification => [self::Proposition, self::Lost],
            self::Proposition => [self::Negotiation, self::Lost],
            self::Negotiation => [self::Won, self::Lost],
            self::Won => [],
            self::Lost => [self::New],
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
