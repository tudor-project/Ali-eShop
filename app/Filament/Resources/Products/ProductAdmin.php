<?php

namespace App\Filament\Resources\Products;

use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use App\Models\VariantOptionValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ProductAdmin
{
    /**
     * @return array<string, string>
     */
    public static function locales(): array
    {
        return [
            'ro' => __('admin.products.locales.ro'),
            'ru' => __('admin.products.locales.ru'),
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

    public static function formatBani(int|string|null $bani): string
    {
        $amount = (int) $bani;
        $sign = $amount < 0 ? '-' : '';
        $amount = abs($amount);

        return $sign.intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT).' MDL';
    }

    /**
     * @return array<string, string>
     */
    public static function optionGroupChoices(mixed $groups): array
    {
        if (! is_array($groups)) {
            return [];
        }

        $choices = [];

        foreach ($groups as $group) {
            if (! is_array($group) || blank($group['code'] ?? null)) {
                continue;
            }

            $code = (string) $group['code'];
            $name = self::nameFromTranslations($group['translations'] ?? []);
            $choices[$code] = $name === '—' ? $code : $name;
        }

        return $choices;
    }

    /**
     * @return array<string, string>
     */
    public static function optionValueChoices(mixed $groups, mixed $groupCode): array
    {
        if (! is_array($groups) || blank($groupCode)) {
            return [];
        }

        $choices = [];

        foreach ($groups as $group) {
            if (! is_array($group) || ($group['code'] ?? null) !== $groupCode) {
                continue;
            }

            foreach ($group['values'] ?? [] as $value) {
                if (! is_array($value) || blank($value['code'] ?? null)) {
                    continue;
                }

                $code = (string) $value['code'];
                $name = self::nameFromTranslations($value['translations'] ?? []);
                $choices[$code] = $name === '—' ? $code : $name;
            }
        }

        return $choices;
    }

    /**
     * @return array<int, array{group_code: string, value_code: string, surcharge: int, is_active: bool}>
     */
    public static function optionLinksForVariant(int $variantId): array
    {
        return VariantOptionValue::query()
            ->where('product_variant_id', $variantId)
            ->with('optionValue.optionGroup')
            ->get()
            ->map(function (VariantOptionValue $link): ?array {
                $groupCode = $link->optionValue?->optionGroup?->code;
                $valueCode = $link->optionValue?->code;

                if (blank($groupCode) || blank($valueCode)) {
                    return null;
                }

                return [
                    'group_code' => $groupCode,
                    'value_code' => $valueCode,
                    'surcharge' => $link->surcharge,
                    'is_active' => $link->is_active,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int|string, mixed>  $links
     */
    public static function syncVariantOptions(ProductVariant $variant, array $links): void
    {
        $variant->variantOptionValues()->delete();

        $attached = [];

        foreach ($links as $link) {
            if (! is_array($link) || blank($link['group_code'] ?? null) || blank($link['value_code'] ?? null)) {
                continue;
            }

            $optionValue = ProductOptionValue::query()
                ->where('code', $link['value_code'])
                ->whereHas('optionGroup', function (Builder $query) use ($variant, $link): void {
                    $query
                        ->where('product_id', $variant->product_id)
                        ->where('code', $link['group_code']);
                })
                ->first();

            if ($optionValue === null || isset($attached[$optionValue->id])) {
                continue;
            }

            $attached[$optionValue->id] = true;

            $variant->variantOptionValues()->create([
                'product_option_value_id' => $optionValue->id,
                'surcharge' => (int) ($link['surcharge'] ?? 0),
                'is_active' => (bool) ($link['is_active'] ?? true),
            ]);
        }
    }
}
