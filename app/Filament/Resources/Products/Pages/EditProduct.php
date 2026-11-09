<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\SyncsProductAssignments;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\InfoPopup;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use SyncsProductAssignments;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $product = $this->getRecord();

        if (! $product instanceof Product) {
            return $data;
        }

        $product->loadMissing(['categories', 'infoPopups']);

        $data['categoryAssignments'] = $product->categories
            ->map(fn (Category $category): array => [
                'category_id' => $category->id,
                'sort_order' => $category->pivot?->sort_order ?? 0,
            ])
            ->all();

        $data['infoPopupAssignments'] = $product->infoPopups
            ->map(fn (InfoPopup $popup): array => [
                'info_popup_id' => $popup->id,
                'sort_order' => $popup->pivot?->sort_order ?? 0,
            ])
            ->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncProductAssignments();
    }
}
