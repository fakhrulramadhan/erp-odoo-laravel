<?php

namespace App\Enums;

enum ScrapType: string
{
    case RawMaterial = 'raw_material';
    case Production = 'production';
    case FinishedGoods = 'finished_goods';
    case Return = 'return';

    public function label(): string
    {
        return match ($this) {
            self::RawMaterial => 'Raw Material Scrap',
            self::Production => 'Production Scrap',
            self::FinishedGoods => 'Finished Goods Scrap',
            self::Return => 'Return Scrap',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RawMaterial => 'orange',
            self::Production => 'yellow',
            self::FinishedGoods => 'red',
            self::Return => 'blue',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
