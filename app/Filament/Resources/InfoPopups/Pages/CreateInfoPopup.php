<?php

namespace App\Filament\Resources\InfoPopups\Pages;

use App\Filament\Resources\InfoPopups\InfoPopupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInfoPopup extends CreateRecord
{
    protected static string $resource = InfoPopupResource::class;

    protected function afterFill(): void
    {
        $this->form->fill([
            'is_active' => true,
            'translations' => [
                ['locale' => 'ro'],
                ['locale' => 'ru'],
            ],
        ]);
    }
}
