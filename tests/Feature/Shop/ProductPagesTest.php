<?php

namespace Tests\Feature\Shop;

use App\Enums\StockPolicy;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\InfoPopup;
use App\Models\InfoPopupTranslation;
use App\Models\Product;
use App\Models\ProductOptionGroup;
use App\Models\ProductOptionGroupTranslation;
use App\Models\ProductOptionValue;
use App\Models\ProductOptionValueTranslation;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_page_lists_active_products_in_romanian(): void
    {
        $this->product('Oglindă mică', 'oglinda-mica', 15000, 2);
        $visible = $this->product('Oglindă mare', 'oglinda-mare', 25000, 1, featured: true);
        $hidden = Product::query()->create(['is_active' => false, 'is_featured' => false, 'sort_order' => 0]);
        ProductTranslation::query()->create([
            'product_id' => $hidden->id,
            'locale' => 'ro',
            'name' => 'Ascunsă',
            'slug' => 'ascunsa',
        ]);

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertSee('Magazin');
        $response->assertSee('Vezi produsul');
        $response->assertSee('Favorite');
        $response->assertSeeInOrder(['Oglindă mare', 'Oglindă mică']);
        $response->assertSee('250,00 MDL');
        $response->assertSee('Top');
        $response->assertDontSee('Ascunsă');
        $response->assertSee(route('products.show', 'oglinda-mare'), false);
        $this->assertTrue($visible->is_featured);
    }

    public function test_shop_page_filters_by_category_and_price(): void
    {
        $category = Category::query()->create(['is_active' => true, 'sort_order' => 1]);
        CategoryTranslation::query()->create([
            'category_id' => $category->id,
            'locale' => 'ro',
            'name' => 'Perete',
            'slug' => 'perete',
        ]);

        $cheap = $this->product('Mică', 'mica', 10000, 2);
        $expensive = $this->product('Mare', 'mare', 40000, 1);
        $cheap->categories()->attach($category->id, ['sort_order' => 1]);

        $this->get(route('products.index', ['category' => 'perete']))
            ->assertOk()
            ->assertSee('Mică')
            ->assertDontSee('Mare');

        $this->get(route('products.index', ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSeeInOrder(['Mică', 'Mare']);

        $this->get(route('products.index', ['q' => 'Mar']))
            ->assertOk()
            ->assertSee('Mare')
            ->assertDontSee('Mică');

        $this->assertTrue($expensive->is_active);
    }

    public function test_product_page_shows_details_options_and_price(): void
    {
        $category = Category::query()->create(['is_active' => true, 'sort_order' => 1]);
        CategoryTranslation::query()->create([
            'category_id' => $category->id,
            'locale' => 'ro',
            'name' => 'Perete',
            'slug' => 'perete',
        ]);

        $product = $this->product('Oglindă rotundă', 'oglinda-rotunda', 19900, 1);
        $product->translations()->where('locale', 'ro')->update([
            'short_description' => 'Ramă subțire.',
            'description' => 'Se montează pe perete.',
        ]);
        $product->categories()->attach($category->id, ['sort_order' => 1]);

        $popup = InfoPopup::query()->create(['key' => 'size-help', 'is_active' => true]);
        InfoPopupTranslation::query()->create([
            'info_popup_id' => $popup->id,
            'locale' => 'ro',
            'title' => 'Despre mărime',
            'body' => 'Alege mărimea ramei.',
        ]);

        $group = ProductOptionGroup::query()->create([
            'product_id' => $product->id,
            'info_popup_id' => $popup->id,
            'code' => 'size',
            'min_selections' => 1,
            'max_selections' => 1,
            'sort_order' => 0,
            'is_active' => true,
        ]);
        ProductOptionGroupTranslation::query()->create([
            'product_option_group_id' => $group->id,
            'locale' => 'ro',
            'name' => 'Mărime',
        ]);
        $value = ProductOptionValue::query()->create([
            'product_option_group_id' => $group->id,
            'code' => 'large',
            'is_active' => true,
            'sort_order' => 0,
        ]);
        ProductOptionValueTranslation::query()->create([
            'product_option_value_id' => $value->id,
            'locale' => 'ro',
            'name' => 'Mare',
        ]);

        $this->get(route('products.show', 'oglinda-rotunda'))
            ->assertOk()
            ->assertSee('Oglindă rotundă')
            ->assertSee('Ramă subțire.')
            ->assertSee('Se montează pe perete.')
            ->assertSee('199,00 MDL')
            ->assertSee('Mărime')
            ->assertSee('Mare')
            ->assertSee('Pentru mai multe informații, apasă simbolul i.')
            ->assertSee('Despre mărime')
            ->assertSee('Alege mărimea ramei.')
            ->assertSee('MIRROR-oglinda-rotunda')
            ->assertSee('Perete');

        $this->get(route('products.show', 'lipsa'))->assertNotFound();
    }

    private function product(string $name, string $slug, int $price, int $sortOrder, bool $featured = false): Product
    {
        $product = Product::query()->create([
            'is_active' => true,
            'is_featured' => $featured,
            'sort_order' => $sortOrder,
        ]);

        ProductTranslation::query()->create([
            'product_id' => $product->id,
            'locale' => 'ro',
            'name' => $name,
            'slug' => $slug,
        ]);

        ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'MIRROR-'.$slug,
            'base_price' => $price,
            'stock_quantity' => 3,
            'stock_policy' => StockPolicy::Track,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return $product;
    }
}
