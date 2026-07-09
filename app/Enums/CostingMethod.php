<?php

namespace App\Enums;

enum CostingMethod: string
{
    case Standard = 'standard';
    case Average = 'average';
    case Fifo = 'fifo';
    case Lifo = 'lifo';
    case WeightedAverage = 'weighted_average';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard Cost',
            self::Average => 'Average Cost',
            self::Fifo => 'FIFO',
            self::Lifo => 'LIFO',
            self::WeightedAverage => 'Weighted Average',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
