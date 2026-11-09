@php
    $searchCategories = \App\Models\Category::query()
        ->where('is_active', true)
        ->whereHas('translations', fn ($query) => $query->where('locale', app()->getLocale()))
        ->with(['translations' => fn ($query) => $query->where('locale', app()->getLocale())])
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
@endphp

<header
    class="border-b border-neutral-200 bg-white"
    x-data="{ menu: false, search: false }"
    x-effect="document.body.classList.toggle('overflow-hidden', search && window.matchMedia('(max-width: 767px)').matches)"
    x-on:keydown.escape.window="menu = false; search = false"
    x-on:open-search.window="search = true; $nextTick(() => $nextTick(() => $refs.mobileQuery?.focus()))"
>
    <div class="flex h-16 items-center gap-3 px-4 sm:px-8 lg:px-12">
        <button
            type="button"
            class="inline-flex size-11 items-center justify-center"
            x-on:click="menu = true"
            aria-controls="shop-menu"
            x-bind:aria-expanded="menu.toString()"
        >
            <span class="sr-only">{{ __('shop.nav.menu') }}</span>
            <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>

        <a href="{{ route('home') }}" class="text-sm font-semibold tracking-[0.22em] uppercase">
            {{ config('app.name') }}
        </a>

        <nav class="ms-6 hidden items-center gap-8 text-sm md:flex" aria-label="{{ __('shop.nav.shop') }}">
            <a href="{{ route('home') }}" @class(['underline decoration-neutral-900 underline-offset-8' => request()->routeIs('home')])>
                {{ __('shop.nav.home') }}
            </a>
            <a href="{{ route('products.index') }}" @class(['underline decoration-neutral-900 underline-offset-8' => request()->routeIs('products.*')])>
                {{ __('shop.nav.shop') }}
            </a>
        </nav>

        <div class="ms-auto flex items-center">
            @auth
                <a href="{{ route('dashboard') }}" class="hidden h-11 items-center px-3 text-sm md:inline-flex">
                    {{ __('shop.nav.account') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="hidden h-11 items-center px-3 text-sm md:inline-flex">
                    {{ __('shop.nav.sign_in') }}
                </a>
            @endauth

            <button
                type="button"
                class="hidden size-11 items-center justify-center md:inline-flex"
                x-on:click="search = ! search; if (search) $nextTick(() => $nextTick(() => $refs.query?.focus()))"
                x-bind:aria-expanded="search.toString()"
            >
                <span class="sr-only">{{ __('shop.nav.search') }}</span>
                <svg viewBox="0 0 24 24" class="size-5 cursor-pointer" fill="currentColor" aria-hidden="true">
                    <path d="M10.5 4a6.5 6.5 0 0 1 5.17 10.47l3.93 3.93-1.2 1.2-3.93-3.93A6.5 6.5 0 1 1 10.5 4m0 1.5a5 5 0 1 0 0 10 5 5 0 0 0 0-10" />
                </svg>
            </button>

            <span class="inline-flex size-11 items-center justify-center" aria-hidden="true">
                <svg viewBox="0 0 24 24" class="size-5 cursor-pointer" fill="currentColor">
                    <path d="M7 7V6a5 5 0 0 1 10 0v1h3.2l-1.7 10.2A2 2 0 0 1 16.52 19H7.48a2 2 0 0 1-1.98-1.8L3.8 7zm2 0h6V6a3 3 0 0 0-6 0zm-3.14 1.5.15 1h11.98l.15-1z" />
                </svg>
            </span>
        </div>
    </div>

    <form
        action="{{ route('products.index') }}"
        method="get"
        class="hidden border-t border-neutral-200 px-4 py-3 sm:px-8 md:block lg:px-12"
        x-cloak
        x-show="search"
    >
        <label class="flex items-center gap-3">
            <span class="sr-only">{{ __('shop.nav.search') }}</span>
            <input
                x-ref="query"
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="{{ __('shop.products.search_placeholder') }}"
                class="h-11 w-full border-b border-neutral-900 bg-transparent text-base outline-none placeholder:text-neutral-400"
            >
            <button type="submit" class="inline-flex h-11 items-center px-2 text-xs font-medium tracking-[0.14em] uppercase">
                {{ __('shop.products.search_submit') }}
            </button>
        </label>
    </form>

    <div
        class="fixed inset-0 z-50 flex flex-col bg-white md:hidden"
        x-cloak
        x-show="search"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('shop.nav.search') }}"
    >
        <div class="flex h-16 items-center justify-between px-5">
            <h2 class="text-xl">{{ __('shop.nav.search') }}</h2>
            <button type="button" class="inline-flex size-11 items-center justify-center" x-on:click="search = false">
                <span class="sr-only">{{ __('shop.nav.close') }}</span>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>

        <div class="flex flex-1 items-center px-5">
            <form action="{{ route('products.index') }}" method="get" class="w-full">
                <label class="block">
                    <span class="sr-only">{{ __('shop.nav.search') }}</span>
                    <input
                        x-ref="mobileQuery"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="{{ __('shop.nav.search_placeholder') }}"
                        autocomplete="off"
                        class="w-full border-b border-neutral-900 bg-transparent py-4 text-2xl outline-none placeholder:text-neutral-400"
                    >
                </label>
            </form>
        </div>

        @if ($searchCategories->isNotEmpty())
            <div class="px-5 pt-5 pb-12">
                <p class="text-xs text-neutral-400">{{ __('shop.nav.quick_links') }}</p>
                <ul class="mt-2 grid grid-cols-2">
                    @foreach ($searchCategories as $category)
                        @php $categoryTranslation = $category->translations->first(); @endphp
                        @if ($categoryTranslation)
                            <li>
                                <a href="{{ route('products.index', ['category' => $categoryTranslation->slug]) }}" class="flex h-11 items-center text-sm text-neutral-600">
                                    {{ $categoryTranslation->name }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div
        id="shop-menu"
        class="fixed inset-0 z-50 bg-white"
        x-cloak
        x-show="menu"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('shop.nav.menu') }}"
    >
        <div class="flex h-16 items-center justify-between px-4">
            <span class="text-sm font-semibold tracking-[0.22em] uppercase">{{ config('app.name') }}</span>
            <button type="button" class="inline-flex size-11 items-center justify-center" x-on:click="menu = false">
                <span class="sr-only">{{ __('shop.nav.close') }}</span>
                <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
        <nav class="flex flex-col px-6 pt-8 text-2xl">
            <a href="{{ route('home') }}" class="inline-flex h-14 items-center">{{ __('shop.nav.home') }}</a>
            <a href="{{ route('products.index') }}" class="inline-flex h-14 items-center">{{ __('shop.nav.shop') }}</a>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex h-14 items-center">{{ __('shop.nav.account') }}</a>
            @else
                <a href="{{ route('login') }}" class="inline-flex h-14 items-center">{{ __('shop.nav.sign_in') }}</a>
            @endauth
        </nav>
    </div>
</header>
