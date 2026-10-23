<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\Pages\ViewProduct;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\InfoPopup;
use App\Models\InfoPopupTranslation;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete_a_product_with_related_records(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate(config('filament-shield.super_admin.name'), 'web'));
        $this->actingAs($admin);

        $category = Category::query()->create(['is_active' => true, 'sort_order' => 1]);
        CategoryTranslation::query()->create([
            'category_id' => $category->id,
            'locale' => 'ru',
            'name' => 'Зеркала',
            'slug' => 'zerkala',
        ]);

        $popup = InfoPopup::query()->create(['key' => 'install', 'is_active' => true]);
        InfoPopupTranslation::query()->create([
            'info_popup_id' => $popup->id,
            'locale' => 'ru',
            'title' => 'Установка',
            'body' => 'Как установить зеркало.',
        ]);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
                'translations' => [
                    [
                        'locale' => 'ro',
                        'name' => 'Oglinda smart',
                        'slug' => 'oglinda-smart',
                        'short_description' => 'Scurt',
                        'description' => 'Descriere',
                    ],
                    [
                        'locale' => 'ru',
                        'name' => 'Умное зеркало',
                        'slug' => 'umnoe-zerkalo',
                    ],
                ],
                'categoryAssignments' => [
                    ['category_id' => $category->id, 'sort_order' => 2],
                ],
                'infoPopupAssignments' => [
                    ['info_popup_id' => $popup->id, 'sort_order' => 4],
                ],
                'optionGroups' => [
                    [
                        'code' => 'size',
                        'is_active' => true,
                        'min_selections' => 1,
                        'max_selections' => 1,
                        'translations' => [
                            ['locale' => 'ro', 'name' => 'Marime'],
                            ['locale' => 'ru', 'name' => 'Размер'],
                        ],
                        'values' => [
                            [
                                'code' => 'large',
                                'is_active' => true,
                                'translations' => [
                                    ['locale' => 'ro', 'name' => 'Mare'],
                                    ['locale' => 'ru', 'name' => 'Большой'],
                                ],
                            ],
                        ],
                    ],
                ],
                'variants' => [
                    [
                        'sku' => 'MIRROR-L',
                        'base_price' => 19900,
                        'stock_quantity' => 5,
                        'stock_policy' => 'track',
                        'is_active' => true,
                        'weight_grams' => 8000,
                        'option_links' => [
                            [
                                'group_code' => 'size',
                                'value_code' => 'large',
                                'surcharge' => 500,
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::query()->where('sort_order', 3)->first();

        $this->assertNotNull($product);
        $this->assertTrue($product->is_featured);
        $this->assertDatabaseHas('product_translations', [
            'product_id' => $product->id,
            'locale' => 'ro',
            'slug' => 'oglinda-smart',
        ]);
        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $category->id,
            'sort_order' => 2,
        ]);
        $this->assertDatabaseHas('product_info_popup', [
            'product_id' => $product->id,
            'info_popup_id' => $popup->id,
            'sort_order' => 4,
        ]);
        $this->assertDatabaseHas('product_option_groups', [
            'product_id' => $product->id,
            'code' => 'size',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'MIRROR-L',
            'base_price' => 19900,
            'weight_grams' => 8000,
        ]);
        $this->assertDatabaseHas('variant_option_values', [
            'surcharge' => 500,
            'is_active' => true,
        ]);

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->assertFormSet([
                'is_featured' => true,
                'sort_order' => 3,
            ])
            ->fillForm([
                'is_featured' => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertFalse($product->refresh()->is_featured);
        $this->assertDatabaseHas('category_product', [
            'product_id' => $product->id,
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('product_translations', [
            'product_id' => $product->id,
            'locale' => 'ro',
            'slug' => 'oglinda-smart',
        ]);
        $this->assertDatabaseHas('product_translations', [
            'product_id' => $product->id,
            'locale' => 'ru',
            'name' => 'Умное зеркало',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'MIRROR-L',
            'base_price' => 19900,
        ]);
        $this->assertDatabaseHas('variant_option_values', [
            'surcharge' => 500,
        ]);

        Livewire::test(ViewProduct::class, ['record' => $product->getKey()])
            ->assertSuccessful();

        Livewire::test(ListProducts::class)
            ->assertCanSeeTableRecords([$product])
            ->searchTable('Умное')
            ->assertCanSeeTableRecords([$product]);

        Livewire::test(EditProduct::class, ['record' => $product->getKey()])
            ->callAction(DeleteAction::class);

        $this->assertSoftDeleted($product);
    }
}
