<?php

namespace App\Enums;

enum PickingType: string
{
    case Incoming = 'incoming';
    case Outgoing = 'outgoing';
    case Internal = 'internal';
    case Dropship = 'dropship';

    public function label(): string
    {
        return match ($this) {
            self::Incoming => 'Incoming Shipment',
            self::Outgoing => 'Outgoing Shipment',
            self::Internal => 'Internal Transfer',
            self::Dropship => 'Dropship',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Incoming => 'green',
            self::Outgoing => 'red',
            self::Internal => 'blue',
            self::Dropship => 'purple',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
