<?php

namespace App\Enums;

enum ManufacturingOrderStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case MaterialReserved = 'material_reserved';
    case InProduction = 'in_production';
    case QualityCheck = 'quality_check';
    case Finished = 'finished';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Confirmed => 'Confirmed',
            self::MaterialReserved => 'Material Reserved',
            self::InProduction => 'In Production',
            self::QualityCheck => 'Quality Check',
            self::Finished => 'Finished',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Confirmed => 'blue',
            self::MaterialReserved => 'indigo',
            self::InProduction => 'yellow',
            self::QualityCheck => 'orange',
            self::Finished => 'green',
            self::Closed => 'green',
            self::Cancelled => 'red',
        };
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::MaterialReserved, self::Cancelled],
            self::MaterialReserved => [self::InProduction, self::Cancelled],
            self::InProduction => [self::QualityCheck, self::Cancelled],
            self::QualityCheck => [self::Finished, self::Cancelled],
            self::Finished => [self::Closed],
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
