<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $locale = app()->getLocale();
        $search = mb_substr(trim($request->string('q')->toString()), 0, 100);
        $categorySlug = $request->string('category')->toString();
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['price_asc', 'price_desc'], true)) {
            $sort = 'default';
        }

        $products = $this->publicProducts()
            ->withMin([
                'variants as listing_price' => fn (Builder $query) => $query->where('is_active', true),
            ], 'base_price')
            ->when($search !== '', function (Builder $query) use ($locale, $search): void {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->whereHas('translations', function (Builder $translations) use ($locale, $like): void {
                    $translations->where('locale', $locale)->where('name', 'like', $like);
                });
            })
            ->when($categorySlug !== '', function (Builder $query) use ($locale, $categorySlug): void {
                $query->whereHas('categories', function (Builder $categories) use ($locale, $categorySlug): void {
                    $categories->where('is_active', true)
                        ->whereHas('translations', function (Builder $translations) use ($locale, $categorySlug): void {
                            $translations->where('locale', $locale)->where('slug', $categorySlug);
                        });
                });
            });

        $products = match ($sort) {
            'price_asc' => $products->orderBy('listing_price')->orderBy('id'),
            'price_desc' => $products->orderByDesc('listing_price')->orderBy('id'),
            default => $products->orderBy('sort_order')->orderBy('id'),
        };

        $products = $products->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('translations', fn (Builder $query) => $query->where('locale', $locale))
            ->with(['translations' => fn (Relation $query) => $query->where('locale', $locale)])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('shop.products.index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'q' => $search,
                'category' => $categorySlug,
                'sort' => $sort,
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $locale = app()->getLocale();

        $product = $this->publicProducts($slug)
            ->withMin([
                'variants as listing_price' => fn (Builder $query) => $query->where('is_active', true),
            ], 'base_price')
            ->with([
                'categories' => fn (Relation $query) => $query
                    ->where('is_active', true)
                    ->orderBy('category_product.sort_order'),
                'categories.translations' => fn (Relation $query) => $query->where('locale', $locale),
            ])
            ->firstOrFail();

        return view('shop.products.show', [
            'product' => $product,
        ]);
    }

    private function publicProducts(?string $slug = null): Builder
    {
        $locale = app()->getLocale();

        return Product::query()
            ->where('is_active', true)
            ->whereHas('translations', function (Builder $query) use ($locale, $slug): void {
                $query->where('locale', $locale);

                if ($slug !== null) {
                    $query->where('slug', $slug);
                }
            })
            ->with([
                'translations' => fn (Relation $query) => $query->where('locale', $locale),
                'images' => fn (Relation $query) => $query->orderBy('sort_order'),
                'variants' => fn (Relation $query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
                'variants.images' => fn (Relation $query) => $query->orderBy('sort_order'),
                'optionGroups' => fn (Relation $query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
                'optionGroups.translations' => fn (Relation $query) => $query->where('locale', $locale),
                'optionGroups.infoPopup' => fn (Relation $query) => $query->where('is_active', true),
                'optionGroups.infoPopup.translations' => fn (Relation $query) => $query->where('locale', $locale),
                'optionGroups.values' => fn (Relation $query) => $query
                    ->where('is_active', true)
                    ->orderBy('sort_order'),
                'optionGroups.values.translations' => fn (Relation $query) => $query->where('locale', $locale),
            ]);
    }
}
