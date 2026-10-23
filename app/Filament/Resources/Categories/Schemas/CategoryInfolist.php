<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Resources\Categories\CategoryAdmin;
use App\Models\Category;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.categories.sections.general'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent_id')
                            ->label(__('admin.categories.fields.parent'))
                            ->placeholder('—')
                            ->formatStateUsing(fn (Category $record): string => $record->parent === null
                                ? '—'
                                : CategoryAdmin::nameFromTranslations($record->parent->translations)),
                        IconEntry::make('is_active')
                            ->label(__('admin.categories.fields.is_active'))
                            ->boolean(),
                        TextEntry::make('sort_order')
                            ->label(__('admin.categories.fields.sort_order'))
                            ->numeric(),
                        TextEntry::make('created_at')
                            ->label(__('admin.categories.fields.created_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.categories.fields.updated_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('deleted_at')
                            ->label(__('admin.categories.fields.deleted_at'))
                            ->dateTime()
                            ->visible(fn (Category $record): bool => $record->trashed())
                            ->placeholder('—'),
                    ]),
                Section::make(__('admin.categories.sections.translations'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('translations')
                            ->label(__('admin.categories.sections.translations'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('locale')
                                    ->label(__('admin.categories.fields.locale'))
                                    ->formatStateUsing(fn (?string $state): string => CategoryAdmin::locales()[$state] ?? (string) $state),
                                TextEntry::make('name')
                                    ->label(__('admin.categories.fields.name')),
                                TextEntry::make('slug')
                                    ->label(__('admin.categories.fields.slug')),
                                TextEntry::make('description')
                                    ->label(__('admin.categories.fields.description'))
                                    ->columnSpanFull()
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
