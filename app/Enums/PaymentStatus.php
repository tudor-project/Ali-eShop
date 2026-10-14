<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Awaiting = 'awaiting';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
