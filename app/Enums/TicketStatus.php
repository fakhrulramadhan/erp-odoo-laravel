<?php

namespace App\Enums;

enum TicketStatus: string
{
    case New = 'new';
    case Open = 'open';
    case InProgress = 'in_progress';
    case Waiting = 'waiting';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Waiting => 'Waiting',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::Open => 'indigo',
            self::InProgress => 'purple',
            self::Waiting => 'yellow',
            self::Resolved => 'green',
            self::Closed => 'gray',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Open, self::Closed],
            self::Open => [self::InProgress, self::Waiting, self::Closed],
            self::InProgress => [self::Waiting, self::Resolved, self::Closed],
            self::Waiting => [self::InProgress, self::Resolved, self::Closed],
            self::Resolved => [self::Closed, self::Open],
            self::Closed => [self::Open],
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
