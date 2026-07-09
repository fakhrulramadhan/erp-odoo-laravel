<?php

namespace App\Enums;

enum ExpenseClaimStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Paid => 'green',
            self::Cancelled => 'gray',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::Approved, self::Rejected, self::Cancelled],
            self::Approved => [self::Paid, self::Cancelled],
            self::Rejected => [],
            self::Paid => [],
            self::Cancelled => [],
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
