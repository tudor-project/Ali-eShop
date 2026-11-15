<?php

namespace App\Filament\Resources\InfoPopups;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class InfoPopupAdmin
{
    /**
     * @return array<string, string>
     */
    public static function locales(): array
    {
        return [
            'ro' => __('admin.info_popups.locales.ro'),
            'ru' => __('admin.info_popups.locales.ru'),
        ];
    }

    public static function titleFromTranslations(iterable $translations): string
    {
        /** @var Collection<int, mixed> $items */
        $items = collect($translations);

        $match = $items->firstWhere('locale', app()->getLocale())
            ?? $items->firstWhere('locale', 'ro')
            ?? $items->first();

        if ($match instanceof Model) {
            $value = $match->getAttribute('title');
        } elseif (is_array($match)) {
            $value = $match['title'] ?? null;
        } else {
            $value = null;
        }

        return filled($value) ? (string) $value : '—';
    }
}
