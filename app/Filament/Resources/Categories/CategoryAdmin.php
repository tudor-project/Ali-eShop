<?php

namespace App\Filament\Resources\Categories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryAdmin
{
    /**
     * @return array<string, string>
     */
    public static function locales(): array
    {
        return [
            'ro' => __('admin.categories.locales.ro'),
            'ru' => __('admin.categories.locales.ru'),
        ];
    }

    public static function nameFromTranslations(iterable $translations, string $attribute = 'name'): string
    {
        /** @var Collection<int, mixed> $items */
        $items = collect($translations);

        $match = $items->firstWhere('locale', app()->getLocale())
            ?? $items->firstWhere('locale', 'ro')
            ?? $items->first();

        if ($match instanceof Model) {
            $value = $match->getAttribute($attribute);
        } elseif (is_array($match)) {
            $value = $match[$attribute] ?? null;
        } else {
            $value = null;
        }

        return filled($value) ? (string) $value : '—';
    }
}
