<?php

namespace App\Filament\Resources\InfoPopups\Schemas;

use App\Filament\Resources\InfoPopups\InfoPopupAdmin;
use App\Models\InfoPopupTranslation;
use App\Support\InfoPopupContent;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InfoPopupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.info_popups.sections.general'))
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('key')
                            ->label(__('admin.info_popups.fields.key')),
                        IconEntry::make('is_active')
                            ->label(__('admin.info_popups.fields.is_active'))
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label(__('admin.info_popups.fields.created_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label(__('admin.info_popups.fields.updated_at'))
                            ->dateTime()
                            ->placeholder('—'),
                    ]),
                Section::make(__('admin.info_popups.sections.translations'))
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('translations')
                            ->label(__('admin.info_popups.sections.translations'))
                            ->columns(2)
                            ->schema([
                                TextEntry::make('locale')
                                    ->label(__('admin.info_popups.fields.locale'))
                                    ->formatStateUsing(fn (?string $state): string => InfoPopupAdmin::locales()[$state] ?? (string) $state),
                                TextEntry::make('title')
                                    ->label(__('admin.info_popups.fields.title')),
                                TextEntry::make('content')
                                    ->label(__('admin.info_popups.fields.content'))
                                    ->columnSpanFull()
                                    ->formatStateUsing(function (mixed $state, mixed $record): string {
                                        $content = $record instanceof InfoPopupTranslation
                                            ? $record->content
                                            : $state;

                                        $summary = InfoPopupContent::summary(is_array($content) ? $content : []);

                                        return $summary !== '' ? $summary : '—';
                                    }),
                            ]),
                    ]),
            ]);
    }
}
