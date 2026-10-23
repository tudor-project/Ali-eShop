<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\StockPolicy;
use App\Filament\Resources\Products\ProductAdmin;
use App\Models\Category;
use App\Models\InfoPopup;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionValue;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Closure;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::generalSection(),
                self::translationsSection(),
                self::categoriesSection(),
                self::infoPopupsSection(),
                self::optionGroupsSection(),
                self::variantsSection(),
                self::imagesSection(),
            ]);
    }

    private static function generalSection(): Section
    {
        return Section::make(__('admin.products.sections.general'))
            ->columnSpanFull()
            ->columns(2)
            ->schema([
                Toggle::make('is_active')
                    ->label(__('admin.products.fields.is_active'))
                    ->default(true),
                Toggle::make('is_featured')
                    ->label(__('admin.products.fields.is_featured'))
                    ->default(false),
                TextInput::make('sort_order')
                    ->label(__('admin.products.fields.sort_order'))
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ]);
    }

    private static function translationsSection(): Section
    {
        return Section::make(__('admin.products.sections.translations'))
            ->description(__('admin.products.hints.translations'))
            ->columnSpanFull()
            ->schema([
                Repeater::make('translations')
                    ->relationship()
                    ->label(__('admin.products.sections.translations'))
                    ->addActionLabel(__('admin.products.actions.add_translation'))
                    ->minItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? ($state['locale'] ?? null))
                    ->columns(2)
                    ->schema([
                        Select::make('locale')
                            ->label(__('admin.products.fields.locale'))
                            ->options(ProductAdmin::locales())
                            ->required()
                            ->distinct()
                            ->native(false),
                        TextInput::make('name')
                            ->label(__('admin.products.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                if (filled($get('slug'))) {
                                    return;
                                }

                                $slug = Str::slug((string) $state);

                                if ($slug !== '') {
                                    $set('slug', $slug);
                                }
                            }),
                        TextInput::make('slug')
                            ->label(__('admin.products.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('admin.products.hints.slug'))
                            ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->rule(self::uniqueSlugRule()),
                        TextInput::make('short_description')
                            ->label(__('admin.products.fields.short_description'))
                            ->maxLength(255)
                            ->nullable()
                            ->rule('nullable')
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('admin.products.fields.description'))
                            ->rows(6)
                            ->columnSpanFull(),
                        Section::make(__('admin.products.sections.seo'))
                            ->columnSpanFull()
                            ->collapsed()
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label(__('admin.products.fields.seo_title'))
                                    ->maxLength(255)
                                    ->nullable()
                                    ->rule('nullable'),
                                Textarea::make('seo_description')
                                    ->label(__('admin.products.fields.seo_description'))
                                    ->rows(3)
                                    ->nullable()
                                    ->rule('nullable'),
                            ]),
                    ]),
            ]);
    }

    private static function categoriesSection(): Section
    {
        return Section::make(__('admin.products.sections.categories'))
            // ->columnSpanFull()
            ->schema([
                Repeater::make('categoryAssignments')
                    ->label(__('admin.products.sections.categories'))
                    ->addActionLabel(__('admin.products.actions.add_category'))
                    ->defaultItems(0)
                    ->dehydrated(false)
                    ->reorderable(false)
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label(__('admin.products.fields.category'))
                            ->options(fn (): array => self::categoryOptions())
                            ->searchable()
                            ->required()
                            ->distinct()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label(__('admin.products.fields.sort_order'))
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }


    private static function infoPopupsSection(): Section
    {
        return Section::make(__('admin.products.sections.info_popups'))
            // ->columnSpanFull()
            ->schema([
                Repeater::make('infoPopupAssignments')
                    ->label(__('admin.products.sections.info_popups'))
                    ->addActionLabel(__('admin.products.actions.add_info_popup'))
                    ->defaultItems(0)
                    ->dehydrated(false)
                    ->reorderable(false)
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        Select::make('info_popup_id')
                            ->label(__('admin.products.fields.info_popup'))
                            ->options(fn (): array => self::infoPopupOptions())
                            ->searchable()
                            ->required()
                            ->distinct()
                            ->native(false),
                        TextInput::make('sort_order')
                            ->label(__('admin.products.fields.sort_order'))
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }

    private static function optionGroupsSection(): Section
    {
        return Section::make(__('admin.products.sections.options'))
            ->description(__('admin.products.hints.options'))
            // ->columnSpanFull()
            ->schema([
                Repeater::make('optionGroups')
                    ->relationship()
                    ->label(__('admin.products.sections.options'))
                    ->addActionLabel(__('admin.products.actions.add_option_group'))
                    ->defaultItems(0)
                    ->orderColumn('sort_order')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['code'] ?? null)
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label(__('admin.products.fields.code'))
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->distinct()
                            ->live(onBlur: true)
                            ->rule(self::uniqueOptionGroupCodeRule()),
                        Toggle::make('is_active')
                            ->label(__('admin.products.fields.is_active'))
                            ->default(true),
                        TextInput::make('min_selections')
                            ->label(__('admin.products.fields.min_selections'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(0)
                            ->required(),
                        TextInput::make('max_selections')
                            ->label(__('admin.products.fields.max_selections'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(255)
                            ->default(1)
                            ->required(),
                        Repeater::make('translations')
                            ->relationship()
                            ->label(__('admin.products.sections.translations'))
                            ->addActionLabel(__('admin.products.actions.add_translation'))
                            ->minItems(1)
                            ->collapsible()
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('locale')
                                    ->label(__('admin.products.fields.locale'))
                                    ->options(ProductAdmin::locales())
                                    ->required()
                                    ->distinct()
                                    ->native(false),
                                TextInput::make('name')
                                    ->label(__('admin.products.fields.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true),
                            ]),
                        Repeater::make('values')
                            ->relationship()
                            ->label(__('admin.products.sections.option_values'))
                            ->addActionLabel(__('admin.products.actions.add_option_value'))
                            ->orderColumn('sort_order')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['code'] ?? null)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('code')
                                    ->label(__('admin.products.fields.code'))
                                    ->required()
                                    ->maxLength(255)
                                    ->alphaDash()
                                    ->distinct()
                                    ->live(onBlur: true)
                                    ->rule(self::uniqueOptionValueCodeRule()),
                                Toggle::make('is_active')
                                    ->label(__('admin.products.fields.is_active'))
                                    ->default(true),
                                Repeater::make('translations')
                                    ->relationship()
                                    ->label(__('admin.products.sections.translations'))
                                    ->addActionLabel(__('admin.products.actions.add_translation'))
                                    ->minItems(1)
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->schema([
                                        Select::make('locale')
                                            ->label(__('admin.products.fields.locale'))
                                            ->options(ProductAdmin::locales())
                                            ->required()
                                            ->distinct()
                                            ->native(false),
                                        TextInput::make('name')
                                            ->label(__('admin.products.fields.name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function variantsSection(): Section
    {
        return Section::make(__('admin.products.sections.variants'))
            ->description(__('admin.products.hints.variants'))
            // ->columnSpanFull()
            ->schema([
                Repeater::make('variants')
                    ->relationship()
                    ->label(__('admin.products.sections.variants'))
                    ->addActionLabel(__('admin.products.actions.add_variant'))
                    ->defaultItems(0)
                    ->minItems(1)
                    ->orderColumn('sort_order')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['sku'] ?? null)
                    ->columns(2)
                    ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                        $variantId = $data['id'] ?? null;
                        $data['option_links'] = is_numeric($variantId)
                            ? ProductAdmin::optionLinksForVariant((int) $variantId)
                            : [];

                        return $data;
                    })
                    ->afterCreate(function (array $data, ProductVariant $record): void {
                        ProductAdmin::syncVariantOptions($record, $data['option_links'] ?? []);
                    })
                    ->afterUpdate(function (array $data, ProductVariant $record): void {
                        ProductAdmin::syncVariantOptions($record, $data['option_links'] ?? []);
                    })
                    ->afterDelete(function (ProductVariant $record): void {
                        $record->images()->get()->each(fn (Model $image): mixed => $image->delete());
                        $record->forceDelete();
                    })
                    ->schema([
                        TextInput::make('sku')
                            ->label(__('admin.products.fields.sku'))
                            ->required()
                            ->maxLength(255)
                            ->unique(table: 'product_variants', column: 'sku', ignoreRecord: true),
                        TextInput::make('base_price')
                            ->label(__('admin.products.fields.base_price'))
                            // ->helperText(__('admin.products.hints.price_bani'))
                            // ->suffix(__('admin.products.units.bani'))
                            ->suffix('MDL')
                            ->integer()
                            ->minValue(0)
                            ->required()
                            ->formatStateUsing(fn ($state) => $state !== null ? (int) $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state !== null ? (int) $state * 100 : null),


                        TextInput::make('stock_quantity')
                            ->label(__('admin.products.fields.stock_quantity'))
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Select::make('stock_policy')
                            ->label(__('admin.products.fields.stock_policy'))
                            ->options([
                                StockPolicy::Track->value => __('admin.products.stock_policies.track'),
                                StockPolicy::Ignore->value => __('admin.products.stock_policies.ignore'),
                            ])
                            ->default(StockPolicy::Track->value)
                            ->required()
                            ->native(false),
                        Toggle::make('is_active')
                            ->label(__('admin.products.fields.is_active'))
                            ->default(true),
                        Section::make(__('admin.products.sections.dimensions'))
                            ->columnSpanFull()
                            ->collapsed()
                            ->columns(2)
                            ->schema([
                                TextInput::make('weight_grams')
                                    ->label(__('admin.products.fields.weight_grams'))
                                    ->suffix(__('admin.products.units.grams'))
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->rule('nullable'),
                                TextInput::make('length_mm')
                                    ->label(__('admin.products.fields.length_mm'))
                                    ->suffix(__('admin.products.units.mm'))
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->rule('nullable'),
                                TextInput::make('width_mm')
                                    ->label(__('admin.products.fields.width_mm'))
                                    ->suffix(__('admin.products.units.mm'))
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->rule('nullable'),
                                TextInput::make('height_mm')
                                    ->label(__('admin.products.fields.height_mm'))
                                    ->suffix(__('admin.products.units.mm'))
                                    ->integer()
                                    ->minValue(0)
                                    ->nullable()
                                    ->rule('nullable'),
                            ]),
                        Repeater::make('option_links')
                            ->label(__('admin.products.sections.variant_options'))
                            ->addActionLabel(__('admin.products.actions.add_variant_option'))
                            ->defaultItems(0)
                            ->collapsible()
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->columns(2)
                            ->schema([
                                Select::make('group_code')
                                    ->label(__('admin.products.fields.option_group'))
                                    ->options(fn (Select $component): array => ProductAdmin::optionGroupChoices(
                                        data_get($component->getLivewire(), 'data.optionGroups'),
                                    ))
                                    ->required()
                                    ->live()
                                    ->native(false),
                                Select::make('value_code')
                                    ->label(__('admin.products.fields.option_value'))
                                    ->options(fn (Select $component, Get $get): array => ProductAdmin::optionValueChoices(
                                        data_get($component->getLivewire(), 'data.optionGroups'),
                                        $get('group_code'),
                                    ))
                                    ->required()
                                    ->native(false),
                                TextInput::make('surcharge')
                                    ->label(__('admin.products.fields.surcharge'))
                                    // ->helperText(__('admin.products.hints.price_bani'))
                                    // ->suffix(__('admin.products.units.bani'))
                                    ->suffix('MDL')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->formatStateUsing(fn ($state) => $state !== null ? (int) $state / 100 : null)
                                    ->dehydrateStateUsing(fn ($state) => $state !== null ? (int) $state * 100 : null)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label(__('admin.products.fields.is_active'))
                                    ->default(true),
                            ]),
                        self::imageRepeater(forVariant: true),
                    ]),
            ]);
    }

    private static function imagesSection(): Section
    {
        return Section::make(__('admin.products.sections.images'))
            ->columnSpanFull()
            ->schema([
                self::imageRepeater(forVariant: false),
            ]);
    }

    private static function imageRepeater(bool $forVariant): Repeater
    {
        $repeater = Repeater::make('images')
            ->label(__($forVariant ? 'admin.products.sections.variant_images' : 'admin.products.sections.images'))
            ->addActionLabel(__('admin.products.actions.add_image'))
            ->defaultItems(0)
            ->relationship(
                modifyQueryUsing: $forVariant
                    ? null
                    : fn (Builder $query): Builder => $query->whereNull('product_variant_id'),
            )
            ->orderColumn('sort_order')
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['alt'] ?? null)
            ->columns(2)
            ->schema([
                FileUpload::make('path')
                    ->label(__('admin.products.fields.image'))
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->required()
                    ->columnSpanFull(),
                Hidden::make('disk')
                    ->default('public'),
                TextInput::make('alt')
                    ->label(__('admin.products.fields.alt'))
                    ->maxLength(255)
                    ->nullable()
                    ->rule('nullable'),
                Toggle::make('is_primary')
                    ->label(__('admin.products.fields.is_primary'))
                    ->default(false),
            ]);

        if ($forVariant) {
            $repeater
                ->columnSpanFull()
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data, ?Model $record): array {
                    if ($record instanceof ProductVariant) {
                        $data['product_id'] = $record->product_id;
                    }

                    $data['disk'] = $data['disk'] ?? 'public';

                    return $data;
                })
                ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                    $data['disk'] = $data['disk'] ?? 'public';

                    return $data;
                });
        }

        return $repeater;
    }

    /**
     * @return array<int, string>
     */
    private static function categoryOptions(): array
    {
        return Category::query()
            ->with('translations')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $category): array => [
                $category->id => ProductAdmin::nameFromTranslations($category->translations),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private static function infoPopupOptions(): array
    {
        return InfoPopup::query()
            ->with('translations')
            ->orderBy('key')
            ->get()
            ->mapWithKeys(fn (InfoPopup $popup): array => [
                $popup->id => ProductAdmin::nameFromTranslations($popup->translations, 'title'),
            ])
            ->all();
    }

    private static function uniqueSlugRule(): Closure
    {
        return function (TextInput $component, Get $get): Closure {
            return function (string $attribute, mixed $value, Closure $fail) use ($component, $get): void {
                $query = ProductTranslation::query()
                    ->where('locale', $get('locale'))
                    ->where('slug', $value);

                $record = $component->getRecord();

                if ($record instanceof ProductTranslation) {
                    $query->whereKeyNot($record->getKey());
                }

                if ($query->exists()) {
                    $fail(__('validation.unique', ['attribute' => $component->getValidationAttribute()]));
                }
            };
        };
    }

    private static function uniqueOptionGroupCodeRule(): Closure
    {
        return function (TextInput $component): Closure {
            return function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                $product = self::ownerProduct($component);

                if (! $product instanceof Product) {
                    return;
                }

                $query = ProductOptionGroup::query()
                    ->where('product_id', $product->id)
                    ->where('code', $value);

                $record = $component->getRecord();

                if ($record instanceof ProductOptionGroup) {
                    $query->whereKeyNot($record->getKey());
                }

                if ($query->exists()) {
                    $fail(__('validation.unique', ['attribute' => $component->getValidationAttribute()]));
                }
            };
        };
    }

    private static function uniqueOptionValueCodeRule(): Closure
    {
        return function (TextInput $component): Closure {
            return function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                $group = $component->getParentRepeater()?->getRecord();

                if (! $group instanceof ProductOptionGroup) {
                    return;
                }

                $query = ProductOptionValue::query()
                    ->where('product_option_group_id', $group->id)
                    ->where('code', $value);

                $record = $component->getRecord();

                if ($record instanceof ProductOptionValue) {
                    $query->whereKeyNot($record->getKey());
                }

                if ($query->exists()) {
                    $fail(__('validation.unique', ['attribute' => $component->getValidationAttribute()]));
                }
            };
        };
    }

    private static function ownerProduct(TextInput $component): ?Product
    {
        $livewire = $component->getLivewire();

        if (! method_exists($livewire, 'getRecord')) {
            return null;
        }

        $record = $livewire->getRecord();

        return $record instanceof Product ? $record : null;
    }
}
