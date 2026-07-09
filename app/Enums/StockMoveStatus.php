<?php

namespace App\Enums;

enum StockMoveStatus: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Confirmed = 'confirmed';
    case Available = 'available';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Waiting => 'Waiting',
            self::Confirmed => 'Confirmed',
            self::Available => 'Available',
            self::Done => 'Done',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Waiting => 'yellow',
            self::Confirmed => 'blue',
            self::Available => 'indigo',
            self::Done => 'green',
            self::Cancelled => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
