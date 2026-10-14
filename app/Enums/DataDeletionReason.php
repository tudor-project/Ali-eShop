<?php

namespace App\Enums;

enum DataDeletionReason: string
{
    case CustomerRequest = 'customer_request';
    case RetentionExpired = 'retention_expired';
}
