<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\CategoryAdmin;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Closure;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::generalSection(),
                self::translationsSection(),
            ]);
    }

    private static function generalSection(): Section
    {
        return Section::make(__('admin.categories.sections.general'))
            ->columnSpanFull()
            ->columns(2)
            ->schema([
                Select::make('parent_id')
                    ->label(__('admin.categories.fields.parent'))
                    ->options(fn (?Category $record): array => self::parentOptions($record))
                    ->placeholder(__('admin.categories.hints.no_parent'))
                    ->searchable()
                    ->native(false)
                    ->nullable()
                    ->columnSpanFull()
                    ->rule(fn (?Category $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                        if (blank($value)) {
                            return;
                        }

                        if (in_array((int) $value, self::excludedParentIds($record), true)) {
                            $fail(__('admin.categories.validation.parent'));
                        }
                    }),
                Toggle::make('is_active')
                    ->label(__('admin.categories.fields.is_active'))
                    ->default(true),
                TextInput::make('sort_order')
                    ->label(__('admin.categories.fields.sort_order'))
                    ->integer()
                    ->minValue(0)
                    ->default(0)
                    ->required(),
            ]);
    }

    private static function translationsSection(): Section
    {
        return Section::make(__('admin.categories.sections.translations'))
            ->description(__('admin.categories.hints.translations'))
            ->columnSpanFull()
            ->schema([
                Repeater::make('translations')
                    ->relationship()
                    ->afterStateHydrated(function (Repeater $component, ?array $state): void {
                        if ($component->getRecord() instanceof Category) {
                            return;
                        }

                        $items = is_array($state) ? $state : [];
                        $hasLocale = collect($items)->contains(
                            fn (mixed $item): bool => is_array($item) && filled($item['locale'] ?? null),
                        );

                        if ($hasLocale) {
                            return;
                        }

                        // $component->state([
                        //     (string) Str::uuid() => ['locale' => 'ro'],
                        //     (string) Str::uuid() => ['locale' => 'ru'],
                        // ]);
                    })
                    ->label(__('admin.categories.sections.translations'))
                    ->addActionLabel(__('admin.categories.actions.add_translation'))
                    ->minItems(1)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? ($state['locale'] ?? null))
                    ->columns(2)
                    ->schema([
                        Select::make('locale')
                            ->label(__('admin.categories.fields.locale'))
                            ->options(CategoryAdmin::locales())
                            ->required()
                            ->distinct()
                            ->native(false),
                        TextInput::make('name')
                            ->label(__('admin.categories.fields.name'))
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
                            ->label(__('admin.categories.fields.slug'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('admin.categories.hints.slug'))
                            ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->rule(self::uniqueSlugRule()),
                        Textarea::make('description')
                            ->label(__('admin.categories.fields.description'))
                            ->rows(4)
                            ->nullable()
                            ->rule('nullable')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function parentOptions(?Category $record): array
    {
        $excluded = self::excludedParentIds($record);

        $categories = Category::query()
            ->with('translations')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $options = [];

        $walk = function (?int $parentId, string $prefix) use (&$walk, $categories, $excluded, &$options): void {
            $children = $categories
                ->filter(function (Category $category) use ($parentId): bool {
                    $categoryParentId = $category->parent_id === null ? null : (int) $category->parent_id;

                    return $categoryParentId === $parentId;
                })
                ->sortBy([
                    ['sort_order', 'asc'],
                    ['id', 'asc'],
                ]);

            foreach ($children as $category) {
                $id = (int) $category->getKey();

                if (in_array($id, $excluded, true)) {
                    continue;
                }

                $name = CategoryAdmin::nameFromTranslations($category->translations);
                $label = $prefix === '' ? $name : $prefix.' / '.$name;
                $options[$id] = $label;
                $walk($id, $label);
            }
        };

        $walk(null, '');

        foreach ($categories as $category) {
            $id = (int) $category->getKey();

            if (isset($options[$id]) || in_array($id, $excluded, true)) {
                continue;
            }

            $options[$id] = CategoryAdmin::nameFromTranslations($category->translations);
        }

        return $options;
    }

    /**
     * @return array<int, int>
     */
    private static function excludedParentIds(?Category $record): array
    {
        if (! $record instanceof Category || $record->getKey() === null) {
            return [];
        }

        $excluded = [(int) $record->getKey()];
        $frontier = $excluded;

        while ($frontier !== []) {
            $children = Category::withTrashed()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();

            $frontier = [];

            foreach ($children as $id) {
                if (! in_array($id, $excluded, true)) {
                    $excluded[] = $id;
                    $frontier[] = $id;
                }
            }
        }

        return $excluded;
    }

    private static function uniqueSlugRule(): Closure
    {
        return function (TextInput $component, Get $get): Closure {
            return function (string $attribute, mixed $value, Closure $fail) use ($component, $get): void {
                $query = CategoryTranslation::query()
                    ->where('locale', $get('locale'))
                    ->where('slug', $value);

                $record = $component->getRecord();

                if ($record instanceof CategoryTranslation) {
                    $query->whereKeyNot($record->getKey());
                }

                if ($query->exists()) {
                    $fail(__('validation.unique', ['attribute' => $component->getValidationAttribute()]));
                }
            };
        };
    }
}
