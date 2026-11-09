<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Resources\Products\ProductAdmin;
use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.products.fields.name'))
                    ->state(fn (Product $record): string => ProductAdmin::nameFromTranslations($record->translations))
                    ->searchable(query: function (Builder $query, string $search): void {
                        $query->where(function (Builder $query) use ($search): void {
                            $query
                                ->whereHas('translations', fn (Builder $translationQuery): Builder => $translationQuery->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('variants', fn (Builder $variantQuery): Builder => $variantQuery->where('sku', 'like', "%{$search}%"));
                        });
                    }),
                IconColumn::make('is_active')
                    ->label(__('admin.products.fields.is_active'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('admin.products.fields.is_featured'))
                    ->boolean(),
                TextColumn::make('variants_count')
                    ->label(__('admin.products.fields.variants_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.products.fields.sort_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.products.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin.products.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
