<?php

namespace App\Enums;

enum OrderStatusType: string
{
    case Workflow = 'workflow';
    case Payment = 'payment';
    case Fulfilment = 'fulfilment';
}
