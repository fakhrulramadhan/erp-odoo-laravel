<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case WaitingApproval = 'waiting_approval';
    case Approved = 'approved';
    case Ordered = 'ordered';
    case PartialReceived = 'partial_received';
    case Received = 'received';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::WaitingApproval => 'Waiting Approval',
            self::Approved => 'Approved',
            self::Ordered => 'Ordered',
            self::PartialReceived => 'Partial Received',
            self::Received => 'Received',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::WaitingApproval => 'yellow',
            self::Approved => 'blue',
            self::Ordered => 'indigo',
            self::PartialReceived => 'orange',
            self::Received => 'green',
            self::Closed => 'green',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::WaitingApproval, self::Cancelled],
            self::WaitingApproval => [self::Approved, self::Cancelled],
            self::Approved => [self::Ordered, self::Cancelled],
            self::Ordered => [self::PartialReceived, self::Received, self::Cancelled],
            self::PartialReceived => [self::Received, self::Closed],
            self::Received => [self::Closed],
            self::Closed => [],
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
