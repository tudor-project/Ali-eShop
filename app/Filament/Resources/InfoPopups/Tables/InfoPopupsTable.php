<?php

namespace App\Filament\Resources\InfoPopups\Tables;

use App\Filament\Resources\InfoPopups\InfoPopupAdmin;
use App\Models\InfoPopup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InfoPopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.info_popups.fields.title'))
                    ->state(fn (InfoPopup $record): string => InfoPopupAdmin::titleFromTranslations($record->translations))
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->whereHas('translations', fn (Builder $translationQuery): Builder => $translationQuery->where('title', 'like', "%{$search}%"));
                    }),
                TextColumn::make('key')
                    ->label(__('admin.info_popups.fields.key'))
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(__('admin.info_popups.fields.is_active'))
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label(__('admin.info_popups.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('key')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
