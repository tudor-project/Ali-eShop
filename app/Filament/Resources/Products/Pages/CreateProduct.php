<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\SyncsProductAssignments;
use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use SyncsProductAssignments;

    protected static string $resource = ProductResource::class;

    protected function afterFill(): void
    {
        $this->form->fill([
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
            'translations' => [
                ['locale' => 'ro'],
                ['locale' => 'ru'],
            ],
        ]);
    }

    protected function afterCreate(): void
    {
        $this->syncProductAssignments();
    }
}
