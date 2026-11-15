<?php

namespace App\Filament\Resources\InfoPopups\Pages;

use App\Filament\Resources\InfoPopups\InfoPopupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInfoPopup extends ViewRecord
{
    protected static string $resource = InfoPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
