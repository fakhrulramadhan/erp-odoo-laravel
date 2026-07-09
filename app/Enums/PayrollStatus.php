<?php

namespace App\Enums;

enum PayrollStatus: string
{
    case Draft = 'draft';
    case Computing = 'computing';
    case Computed = 'computed';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Computing => 'Computing',
            self::Computed => 'Computed',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
            self::Paid => 'Paid',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Computing => 'blue',
            self::Computed => 'indigo',
            self::PendingApproval => 'yellow',
            self::Approved => 'green',
            self::Paid => 'green',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Computing, self::Cancelled],
            self::Computing => [self::Computed, self::Cancelled],
            self::Computed => [self::PendingApproval, self::Cancelled],
            self::PendingApproval => [self::Approved, self::Cancelled],
            self::Approved => [self::Paid, self::Cancelled],
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
