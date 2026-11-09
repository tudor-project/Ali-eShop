<?php

namespace App\Models;

use App\Enums\OrderAddressType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'type',
    'recipient_name',
    'recipient_phone',
    'address_line',
    'city',
    'region',
    'postal_code',
])]
class OrderAddress extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OrderAddressType::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
