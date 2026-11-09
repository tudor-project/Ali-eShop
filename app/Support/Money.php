<?php

namespace App\Support;

class Money
{
    public static function format(int|string|null $bani): ?string
    {
        if ($bani === null || $bani === '') {
            return null;
        }

        return number_format(((int) $bani) / 100, 2, ',', ' ').' '.__('shop.currency');
    }
}
