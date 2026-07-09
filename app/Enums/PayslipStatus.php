<?php

namespace App\Enums;

enum PayslipStatus: string
{
    case Draft = 'draft';
    case Verified = 'verified';
    case Approved = 'approved';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Verified => 'Verified',
            self::Approved => 'Approved',
            self::Paid => 'Paid',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Verified => 'blue',
            self::Approved => 'green',
            self::Paid => 'green',
            self::Rejected => 'red',
            self::Cancelled => 'gray',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Verified, self::Cancelled],
            self::Verified => [self::Approved, self::Rejected],
            self::Approved => [self::Paid],
            self::Paid => [],
            self::Rejected => [self::Draft],
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
