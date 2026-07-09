<?php

namespace App\Enums;

enum CostMethod: string
{
    case Average = 'average';
    case Standard = 'standard';
    case Fifo = 'fifo';
    case Lifo = 'lifo';

    public function label(): string
    {
        return match ($this) {
            self::Average => 'Average Cost (AVCO)',
            self::Standard => 'Standard Price',
            self::Fifo => 'FIFO',
            self::Lifo => 'LIFO',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
