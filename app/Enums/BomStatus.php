<?php

namespace App\Enums;

enum BomStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Obsolete = 'obsolete';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::UnderReview => 'Under Review',
            self::Approved => 'Approved',
            self::Obsolete => 'Obsolete',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'green',
            self::UnderReview => 'yellow',
            self::Approved => 'blue',
            self::Obsolete => 'red',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active, self::UnderReview, self::Cancelled],
            self::Active => [self::UnderReview, self::Obsolete],
            self::UnderReview => [self::Approved, self::Active],
            self::Approved => [self::Active, self::Obsolete],
            self::Obsolete => [],
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
