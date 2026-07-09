<?php

namespace App\Enums;

enum SalaryComponentType: string
{
    case Basic = 'basic';
    case Allowance = 'allowance';
    case Deduction = 'deduction';
    case Overtime = 'overtime';
    case Bonus = 'bonus';
    case Tax = 'tax';
    case Insurance = 'insurance';
    case Bpjs = 'bpjs';

    public function label(): string
    {
        return match ($this) {
            self::Basic => 'Basic Salary',
            self::Allowance => 'Allowance',
            self::Deduction => 'Deduction',
            self::Overtime => 'Overtime',
            self::Bonus => 'Bonus',
            self::Tax => 'Tax',
            self::Insurance => 'Insurance',
            self::Bpjs => 'BPJS',
        };
    }

    public function isPositive(): bool
    {
        return in_array($this, [self::Basic, self::Allowance, self::Overtime, self::Bonus]);
    }

    public function isNegative(): bool
    {
        return in_array($this, [self::Deduction, self::Tax, self::Insurance, self::Bpjs]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
