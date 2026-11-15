<?php

namespace App\Filament\Resources\InfoPopups;

use App\Filament\Resources\InfoPopups\Pages\CreateInfoPopup;
use App\Filament\Resources\InfoPopups\Pages\EditInfoPopup;
use App\Filament\Resources\InfoPopups\Pages\ListInfoPopups;
use App\Filament\Resources\InfoPopups\Pages\ViewInfoPopup;
use App\Filament\Resources\InfoPopups\Schemas\InfoPopupForm;
use App\Filament\Resources\InfoPopups\Schemas\InfoPopupInfolist;
use App\Filament\Resources\InfoPopups\Tables\InfoPopupsTable;
use App\Models\InfoPopup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InfoPopupResource extends Resource
{
    protected static ?string $model = InfoPopup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    public static function getNavigationLabel(): string
    {
        return __('admin.info_popups.navigation');
    }

    public static function getModelLabel(): string
    {
        return __('admin.info_popups.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.info_popups.plural');
    }

    public static function hasRecordTitle(): bool
    {
        return true;
    }

    public static function getRecordTitle(?Model $record): ?string
    {
        if (! $record instanceof InfoPopup) {
            return null;
        }

        $record->loadMissing('translations');

        return InfoPopupAdmin::titleFromTranslations($record->translations);
    }

    public static function form(Schema $schema): Schema
    {
        return InfoPopupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InfoPopupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfoPopupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfoPopups::route('/'),
            'create' => CreateInfoPopup::route('/create'),
            'view' => ViewInfoPopup::route('/{record}'),
            'edit' => EditInfoPopup::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['translations']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->with(['translations']);
    }
}
