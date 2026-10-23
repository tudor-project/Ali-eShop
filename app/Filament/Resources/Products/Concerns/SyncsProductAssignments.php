<?php

namespace App\Filament\Resources\Products\Concerns;

use App\Models\Product;

trait SyncsProductAssignments
{
    protected function syncProductAssignments(): void
    {
        $product = $this->getRecord();

        if (! $product instanceof Product) {
            return;
        }

        $state = $this->form->getRawState();

        $product->categories()->sync($this->assignmentMap($state['categoryAssignments'] ?? [], 'category_id'));
        $product->infoPopups()->sync($this->assignmentMap($state['infoPopupAssignments'] ?? [], 'info_popup_id'));
    }

    /**
     * @param  array<int|string, mixed>  $rows
     * @return array<int, array{sort_order: int}>
     */
    private function assignmentMap(mixed $rows, string $idKey): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $map = [];

        foreach ($rows as $row) {
            if (! is_array($row) || blank($row[$idKey] ?? null)) {
                continue;
            }

            $map[(int) $row[$idKey]] = [
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ];
        }

        return $map;
    }
}
