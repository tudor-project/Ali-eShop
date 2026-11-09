<x-shop.layout :title="__('shop.products.title')">
    <div
        class="px-4 my-8 sm:max-[992px]:px-8 min-[992px]:px-35 min-[992px]:my-28"
        x-data="{ filters: false }"
        x-effect="document.body.classList.toggle('overflow-hidden', filters && window.matchMedia('(max-width: 991px)').matches)"
        x-on:keydown.escape.window="filters = false"
    >
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
            <h1 class="text-2xl font-medium tracking-tight">{{ __('shop.products.title') }}</h1>
            <nav class="text-xs text-neutral-400" aria-label="{{ __('shop.products.breadcrumb') }}">
                <a href="{{ route('home') }}" class="hover:text-neutral-700">{{ __('shop.nav.home') }}</a>
                <span aria-hidden="true"> / </span>
                <span class="text-neutral-500">{{ __('shop.nav.shop') }}</span>
            </nav>
        </div>

        @if ($filters['q'] !== '')
            <p class="mt-4 text-sm text-neutral-500">{{ __('shop.products.results_for', ['query' => $filters['q']]) }}</p>
        @endif

        <div class="relative mt-10">
        <div class="flex items-center justify-between gap-4 pb-4">
            <button
                type="button"
                class="relative z-40 inline-flex h-11 shrink-0 items-center gap-2 text-sm"
                x-on:click="filters = ! filters"
                x-bind:aria-expanded="filters.toString()"
                aria-controls="shop-filters"
            >
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M4 7h16M7 12h10M10 17h4" />
                </svg>
                {{ __('shop.products.filter') }}
            </button>

            <div class="flex items-center gap-4">
                <p class="hidden text-xs text-neutral-400 sm:block">
                    {{ trans_choice('shop.products.count', $products->count(), ['count' => $products->count()]) }}
                </p>

                <form method="get" action="{{ route('products.index') }}">
                    @if ($filters['q'] !== '')
                        <input type="hidden" name="q" value="{{ $filters['q'] }}">
                    @endif
                    @if ($filters['category'] !== '')
                        <input type="hidden" name="category" value="{{ $filters['category'] }}">
                    @endif
                    <label class="sr-only" for="product-sort">{{ __('shop.products.sort') }}</label>
                    <select
                        id="product-sort"
                        name="sort"
                        class="h-11 bg-transparent text-sm outline-none border-b"
                        onchange="this.form.submit()"
                    >
                        <option value="default" @selected($filters['sort'] === 'default')>{{ __('shop.products.sort_default') }}</option>
                        <option value="price_asc" @selected($filters['sort'] === 'price_asc')>{{ __('shop.products.sort_price_asc') }}</option>
                        <option value="price_desc" @selected($filters['sort'] === 'price_desc')>{{ __('shop.products.sort_price_desc') }}</option>
                    </select>
                </form>
            </div>
        </div>

        <div
            id="shop-filters"
            class="absolute top-0 -inset-x-35 z-30 hidden bg-white px-35 pt-16 pb-10 shadow-[0_8px_32px_-8px_rgba(0,0,0,0.1)] min-[992px]:block"
            x-cloak
            x-show="filters"
            role="region"
            aria-label="{{ __('shop.products.filter') }}"
        >
            <button
                type="button"
                class="absolute top-0 end-35 inline-flex h-11 items-center gap-2 text-sm text-neutral-500"
                x-on:click="filters = false"
            >
                {{ __('shop.nav.close') }}
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>

            <form method="get" action="{{ route('products.index') }}">
                @if ($filters['q'] !== '')
                    <input type="hidden" name="q" value="{{ $filters['q'] }}">
                @endif

                <div class="flex flex-wrap gap-x-20 gap-y-10">
                    <fieldset>
                        <legend class="text-xs font-medium tracking-[0.14em] uppercase">{{ __('shop.products.categories') }}</legend>
                        <div class="mt-5 grid gap-1">
                            <label class="cursor-pointer py-1 text-sm text-neutral-500 has-checked:text-neutral-950 has-checked:underline">
                                <input type="radio" name="category" value="" class="sr-only" @checked($filters['category'] === '')>
                                {{ __('shop.products.all_categories') }}
                            </label>
                            @foreach ($categories as $category)
                                @php $categoryTranslation = $category->translations->first(); @endphp
                                <label class="cursor-pointer py-1 text-sm text-neutral-500 has-checked:text-neutral-950 has-checked:underline">
                                    <input type="radio" name="category" value="{{ $categoryTranslation->slug }}" class="sr-only" @checked($filters['category'] === $categoryTranslation->slug)>
                                    {{ $categoryTranslation->name }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend class="text-xs font-medium tracking-[0.14em] uppercase">{{ __('shop.products.sort_by') }}</legend>
                        <div class="mt-5 grid gap-1">
                            @foreach (['default' => 'sort_default', 'price_asc' => 'sort_price_asc', 'price_desc' => 'sort_price_desc'] as $sort => $label)
                                <label class="cursor-pointer py-1 text-sm text-neutral-500 has-checked:text-neutral-950 has-checked:underline">
                                    <input type="radio" name="sort" value="{{ $sort }}" class="sr-only" @checked($filters['sort'] === $sort)>
                                    {{ __('shop.products.'.$label) }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                </div>

                <div class="mt-10 flex flex-wrap items-center gap-6">
                    <button type="submit" class="inline-flex h-14 w-full max-w-[280px] items-center justify-center bg-neutral-950 text-xs font-medium tracking-[0.14em] text-white uppercase">
                        {{ __('shop.products.filter') }}
                    </button>
                    <a
                        href="{{ route('products.index', array_filter(['q' => $filters['q'] ?: null])) }}"
                        class="inline-flex h-14 w-full max-w-[280px] items-center justify-center border border-neutral-950 text-xs font-medium tracking-[0.14em] uppercase"
                    >
                        {{ __('shop.products.reset_filter') }}
                    </a>
                </div>
            </form>
        </div>
        </div>

        <div
            class="fixed inset-0 z-50 bg-white min-[992px]:!hidden"
            x-cloak
            x-show="filters"
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('shop.products.filter') }}"
        >
            <div class="flex h-16 items-center justify-between border-b border-neutral-200 px-4">
                <h2 class="text-sm font-medium">{{ __('shop.products.filter') }}</h2>
                <button type="button" class="inline-flex size-11 items-center justify-center" x-on:click="filters = false">
                    <span class="sr-only">{{ __('shop.nav.close') }}</span>
                    <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>
            <div class="px-4 py-6">
                <p class="text-xs tracking-[0.14em] text-neutral-400 uppercase">{{ __('shop.products.categories') }}</p>
                <ul class="mt-4">
                    <li>
                        <a
                            href="{{ route('products.index', array_filter(['q' => $filters['q'], 'sort' => $filters['sort'] === 'default' ? null : $filters['sort']])) }}"
                            @class(['flex h-12 items-center text-sm', 'font-medium' => $filters['category'] === ''])
                        >
                            {{ __('shop.products.all_categories') }}
                        </a>
                    </li>
                    @foreach ($categories as $category)
                        @php $categoryTranslation = $category->translations->first(); @endphp
                        <li>
                            <a
                                href="{{ route('products.index', array_filter(['category' => $categoryTranslation->slug, 'q' => $filters['q'] ?: null, 'sort' => $filters['sort'] === 'default' ? null : $filters['sort']])) }}"
                                @class(['flex h-12 items-center text-sm', 'font-medium' => $filters['category'] === $categoryTranslation->slug])
                            >
                                {{ $categoryTranslation->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if ($products->isEmpty())
            <p class="py-20 text-center text-sm text-neutral-500">
                {{ $filters['q'] !== '' || $filters['category'] !== '' ? __('shop.products.empty_filtered') : __('shop.products.empty') }}
            </p>
        @else
            <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-5 md:max-[992px]:grid-cols-3 md:gap-x-10 md:gap-y-10 min-[992px]:grid-cols-4">
                @foreach ($products as $product)
                    <x-shop.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</x-shop.layout>
