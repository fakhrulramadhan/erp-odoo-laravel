<?php

namespace App\Enums;

enum LeadSource: string
{
    case Website = 'website';
    case Phone = 'phone';
    case Email = 'email';
    case Referral = 'referral';
    case SocialMedia = 'social_media';
    case Advertisement = 'advertisement';
    case Exhibition = 'exhibition';
    case ColdCall = 'cold_call';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'Website',
            self::Phone => 'Phone',
            self::Email => 'Email',
            self::Referral => 'Referral',
            self::SocialMedia => 'Social Media',
            self::Advertisement => 'Advertisement',
            self::Exhibition => 'Exhibition',
            self::ColdCall => 'Cold Call',
            self::Other => 'Other',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
