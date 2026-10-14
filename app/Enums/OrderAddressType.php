<?php

namespace App\Enums;

enum OrderAddressType: string
{
    case Shipping = 'shipping';
    case Billing = 'billing';
}
