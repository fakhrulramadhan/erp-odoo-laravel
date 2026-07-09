<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Todo => 'To Do',
            self::InProgress => 'In Progress',
            self::InReview => 'In Review',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Backlog => 'gray',
            self::Todo => 'blue',
            self::InProgress => 'indigo',
            self::InReview => 'purple',
            self::Done => 'green',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Backlog => [self::Todo, self::Cancelled],
            self::Todo => [self::InProgress, self::Cancelled],
            self::InProgress => [self::InReview, self::Todo, self::Cancelled],
            self::InReview => [self::Done, self::InProgress],
            self::Done => [],
            self::Cancelled => [self::Todo],
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
