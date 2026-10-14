<?php

namespace App\Enums;

enum StockPolicy: string
{
    case Track = 'track';
    case Ignore = 'ignore';
}
