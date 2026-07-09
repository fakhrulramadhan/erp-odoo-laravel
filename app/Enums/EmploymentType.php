<?php

namespace App\Enums;

enum EmploymentType: string
{
    case FullTime = 'full_time';
    case PartTime = 'part_time';
    case Contract = 'contract';
    case Internship = 'internship';
    case Freelance = 'freelance';
    case Outsourcing = 'outsourcing';

    public function label(): string
    {
        return match ($this) {
            self::FullTime => 'Full Time',
            self::PartTime => 'Part Time',
            self::Contract => 'Contract',
            self::Internship => 'Internship',
            self::Freelance => 'Freelance',
            self::Outsourcing => 'Outsourcing',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
