<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\StockPolicy;
use App\Filament\Resources\Products\ProductAdmin;
use App\Models\Category;
use App\Models\InfoPopup;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionValue;
use App\Models\VariantOptionValue;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.products.sections.general'))
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        IconEntry::make('is_active')
                            ->label(__('admin.products.fields.is_active'))
                            ->boolean(),
                        IconEntry::make('is_featured')
                            ->label(__('admin.products.fields.is_featured'))
                            ->boolean(),
                        TextEntry::make('sort_order')
                            ->label(__('admin.products.fields.sort_order'))
                            ->numeric(),
                        TextEntry::make('created_at')
                            ->label(__('admin.products.fields.created_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.products.fields.updated_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('deleted_at')
                            ->label(__('admin.products.fields.deleted_at'))
                            ->dateTime()
                            ->visible(fn (Product $record): bool => $record->trashed())
                            ->placeholder('—'),
                    ]),
                Section::make(__('admin.products.sections.translations'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('translations')
                            ->label(__('admin.products.sections.translations'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('locale')
                                    ->label(__('admin.products.fields.locale'))
                                    ->formatStateUsing(fn (?string $state): string => ProductAdmin::locales()[$state] ?? (string) $state),
                                TextEntry::make('name')
                                    ->label(__('admin.products.fields.name')),
                                TextEntry::make('slug')
                                    ->label(__('admin.products.fields.slug')),
                                TextEntry::make('short_description')
                                    ->label(__('admin.products.fields.short_description'))
                                    ->placeholder('—'),
                                TextEntry::make('description')
                                    ->label(__('admin.products.fields.description'))
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                                TextEntry::make('seo_title')
                                    ->label(__('admin.products.fields.seo_title'))
                                    ->placeholder('—'),
                                TextEntry::make('seo_description')
                                    ->label(__('admin.products.fields.seo_description'))
                                    ->placeholder('—'),
                            ]),
                    ]),
                Section::make(__('admin.products.sections.categories'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('categoryAssignments')
                            ->label(__('admin.products.sections.categories'))
                            ->columns(2)
                            ->getConstantStateUsing(fn (Product $record): array => $record->categories
                                ->map(fn (Category $category): array => [
                                    'name' => ProductAdmin::nameFromTranslations($category->translations),
                                    'sort_order' => $category->pivot?->sort_order,
                                ])
                                ->all())
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('admin.products.fields.category')),
                                TextEntry::make('sort_order')
                                    ->label(__('admin.products.fields.sort_order')),
                            ]),
                    ]),
                Section::make(__('admin.products.sections.info_popups'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('infoPopupAssignments')
                            ->label(__('admin.products.sections.info_popups'))
                            ->columns(2)
                            ->getConstantStateUsing(fn (Product $record): array => $record->infoPopups
                                ->map(fn (InfoPopup $popup): array => [
                                    'title' => ProductAdmin::nameFromTranslations($popup->translations, 'title'),
                                    'sort_order' => $popup->pivot?->sort_order,
                                ])
                                ->all())
                            ->schema([
                                TextEntry::make('title')
                                    ->label(__('admin.products.fields.info_popup')),
                                TextEntry::make('sort_order')
                                    ->label(__('admin.products.fields.sort_order')),
                            ]),
                    ]),
                Section::make(__('admin.products.sections.options'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('optionGroups')
                            ->label(__('admin.products.sections.options'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('admin.products.fields.code')),
                                IconEntry::make('is_active')
                                    ->label(__('admin.products.fields.is_active'))
                                    ->boolean(),
                                TextEntry::make('min_selections')
                                    ->label(__('admin.products.fields.min_selections')),
                                TextEntry::make('max_selections')
                                    ->label(__('admin.products.fields.max_selections')),
                                TextEntry::make('sort_order')
                                    ->label(__('admin.products.fields.sort_order')),
                                TextEntry::make('name')
                                    ->label(__('admin.products.fields.name'))
                                    ->getConstantStateUsing(fn (ProductOptionGroup $record): string => ProductAdmin::nameFromTranslations($record->translations)),
                                RepeatableEntry::make('values')
                                    ->label(__('admin.products.sections.option_values'))
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('code')
                                            ->label(__('admin.products.fields.code')),
                                        TextEntry::make('name')
                                            ->label(__('admin.products.fields.name'))
                                            ->getConstantStateUsing(fn (ProductOptionValue $record): string => ProductAdmin::nameFromTranslations($record->translations)),
                                        IconEntry::make('is_active')
                                            ->label(__('admin.products.fields.is_active'))
                                            ->boolean(),
                                        TextEntry::make('sort_order')
                                            ->label(__('admin.products.fields.sort_order')),
                                    ]),
                            ]),
                    ]),
                Section::make(__('admin.products.sections.variants'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('variants')
                            ->label(__('admin.products.sections.variants'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('sku')
                                    ->label(__('admin.products.fields.sku')),
                                TextEntry::make('base_price')
                                    ->label(__('admin.products.fields.base_price'))
                                    ->formatStateUsing(fn (int|string|null $state): string => ProductAdmin::formatBani($state)),
                                TextEntry::make('stock_quantity')
                                    ->label(__('admin.products.fields.stock_quantity')),
                                TextEntry::make('stock_policy')
                                    ->label(__('admin.products.fields.stock_policy'))
                                    ->formatStateUsing(function (StockPolicy|string|null $state): string {
                                        $value = $state instanceof StockPolicy ? $state->value : (string) $state;

                                        return __('admin.products.stock_policies.'.$value);
                                    }),
                                IconEntry::make('is_active')
                                    ->label(__('admin.products.fields.is_active'))
                                    ->boolean(),
                                TextEntry::make('sort_order')
                                    ->label(__('admin.products.fields.sort_order')),
                                TextEntry::make('weight_grams')
                                    ->label(__('admin.products.fields.weight_grams'))
                                    ->placeholder('—'),
                                TextEntry::make('length_mm')
                                    ->label(__('admin.products.fields.length_mm'))
                                    ->placeholder('—'),
                                TextEntry::make('width_mm')
                                    ->label(__('admin.products.fields.width_mm'))
                                    ->placeholder('—'),
                                TextEntry::make('height_mm')
                                    ->label(__('admin.products.fields.height_mm'))
                                    ->placeholder('—'),
                                RepeatableEntry::make('variantOptionValues')
                                    ->label(__('admin.products.sections.variant_options'))
                                    ->columnSpanFull()
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('option')
                                            ->label(__('admin.products.fields.option_value'))
                                            ->getConstantStateUsing(function (VariantOptionValue $record): string {
                                                $group = ProductAdmin::nameFromTranslations($record->optionValue?->optionGroup?->translations ?? []);
                                                $value = ProductAdmin::nameFromTranslations($record->optionValue?->translations ?? []);

                                                return $group.' / '.$value;
                                            }),
                                        TextEntry::make('surcharge')
                                            ->label(__('admin.products.fields.surcharge'))
                                            ->formatStateUsing(fn (int|string|null $state): string => ProductAdmin::formatBani($state)),
                                        IconEntry::make('is_active')
                                            ->label(__('admin.products.fields.is_active'))
                                            ->boolean(),
                                    ]),
                                RepeatableEntry::make('images')
                                    ->label(__('admin.products.sections.variant_images'))
                                    ->columnSpanFull()
                                    ->schema(self::imageEntries()),
                            ]),
                    ]),
                Section::make(__('admin.products.sections.images'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('gallery')
                            ->label(__('admin.products.sections.images'))
                            ->getConstantStateUsing(fn (Product $record): array => $record->images
                                ->filter(fn (ProductImage $image): bool => $image->product_variant_id === null)
                                ->values()
                                ->all())
                            ->schema(self::imageEntries()),
                    ]),
            ]);
    }

    /**
     * @return array<int, ImageEntry|IconEntry|TextEntry>
     */
    private static function imageEntries(): array
    {
        return [
            ImageEntry::make('path')
                ->label(__('admin.products.fields.image'))
                ->disk('public')
                ->imageHeight(80),
            TextEntry::make('alt')
                ->label(__('admin.products.fields.alt'))
                ->placeholder('—'),
            IconEntry::make('is_primary')
                ->label(__('admin.products.fields.is_primary'))
                ->boolean(),
            TextEntry::make('sort_order')
                ->label(__('admin.products.fields.sort_order')),
        ];
    }
}
