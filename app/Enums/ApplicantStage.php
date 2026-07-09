<?php

namespace App\Enums;

enum ApplicantStage: string
{
    case New = 'new';
    case Screening = 'screening';
    case Interview = 'interview';
    case TechnicalTest = 'technical_test';
    case Offer = 'offer';
    case Hired = 'hired';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Screening => 'Screening',
            self::Interview => 'Interview',
            self::TechnicalTest => 'Technical Test',
            self::Offer => 'Offer',
            self::Hired => 'Hired',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'blue',
            self::Screening => 'indigo',
            self::Interview => 'purple',
            self::TechnicalTest => 'orange',
            self::Offer => 'green',
            self::Hired => 'green',
            self::Rejected => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::New => [self::Screening, self::Rejected],
            self::Screening => [self::Interview, self::Rejected],
            self::Interview => [self::TechnicalTest, self::Offer, self::Rejected],
            self::TechnicalTest => [self::Offer, self::Rejected],
            self::Offer => [self::Hired, self::Rejected],
            self::Hired => [],
            self::Rejected => [self::New],
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
