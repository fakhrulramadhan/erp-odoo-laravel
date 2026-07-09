<?php

namespace App\Enums;

enum DepreciationMethod: string
{
    case StraightLine = 'straight_line';
    case DecliningBalance = 'declining_balance';
    case DoubleDecliningBalance = 'double_declining_balance';
    case UnitsOfProduction = 'units_of_production';
    case SumOfYearsDigits = 'sum_of_years_digits';

    public function label(): string
    {
        return match ($this) {
            self::StraightLine => 'Straight Line',
            self::DecliningBalance => 'Declining Balance',
            self::DoubleDecliningBalance => 'Double Declining Balance',
            self::UnitsOfProduction => 'Units of Production',
            self::SumOfYearsDigits => 'Sum of Years Digits',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
