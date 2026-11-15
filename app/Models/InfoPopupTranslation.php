<?php

namespace App\Models;

use App\Support\InfoPopupContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['info_popup_id', 'locale', 'title', 'content'])]
class InfoPopupTranslation extends Model
{
    protected $attributes = [
        'content' => '[]',
    ];

    public function infoPopup(): BelongsTo
    {
        return $this->belongsTo(InfoPopup::class);
    }

    public function hasBlocks(): bool
    {
        return InfoPopupContent::normalize($this->content) !== [];
    }

    /**
     * @return Attribute<array<int, array{type: string, data: array<string, mixed>}>, string>
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                if (is_array($value)) {
                    return $value;
                }

                if (! is_string($value) || $value === '') {
                    return [];
                }

                $decoded = json_decode($value, true);

                return is_array($decoded) ? $decoded : [];
            },
            set: fn (mixed $value): string => json_encode(
                InfoPopupContent::normalize($value),
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            ),
        );
    }
}
