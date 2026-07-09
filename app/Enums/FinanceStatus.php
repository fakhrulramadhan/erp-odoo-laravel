<?php

namespace App\Enums;

enum FinanceStatus: string
{
    case Draft = 'draft';
    case Posted = 'posted';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
}
