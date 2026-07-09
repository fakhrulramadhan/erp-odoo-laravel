<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Draft = 'draft';
    case CheckedIn = 'checked_in';
    case CheckedOut = 'checked_out';
    case Absent = 'absent';
    case Late = 'late';
    case OnLeave = 'on_leave';
    case Holiday = 'holiday';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::CheckedIn => 'Checked In',
            self::CheckedOut => 'Checked Out',
            self::Absent => 'Absent',
            self::Late => 'Late',
            self::OnLeave => 'On Leave',
            self::Holiday => 'Holiday',
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::CheckedIn => 'blue',
            self::CheckedOut => 'green',
            self::Absent => 'red',
            self::Late => 'orange',
            self::OnLeave => 'yellow',
            self::Holiday => 'purple',
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
