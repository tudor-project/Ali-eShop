<?php

namespace App\Enums;

enum NotificationDispatchStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
