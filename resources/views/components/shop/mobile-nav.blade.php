<nav x-data class="fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 border-t border-neutral-200 bg-white md:hidden" aria-label="{{ __('shop.nav.shop') }}">
    <a href="{{ route('home') }}" class="flex h-16 flex-col items-center justify-center gap-1 text-[11px]">
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M4 11.5 12 4l8 7.5V20H4z" />
        </svg>
        {{ __('shop.nav.home')}}
    </a>
    <button type="button" class="flex h-16 flex-col items-center justify-center gap-1 text-[11px]" x-on:click="$dispatch('open-search')">
        <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
            <path d="M10.5 4a6.5 6.5 0 0 1 5.17 10.47l3.93 3.93-1.2 1.2-3.93-3.93A6.5 6.5 0 1 1 10.5 4m0 1.5a5 5 0 1 0 0 10 5 5 0 0 0 0-10" />
        </svg>
        {{ __('shop.nav.search') }}
    </button>
    <a href="{{ route('products.index') }}" class="flex h-16 flex-col items-center justify-center gap-1 text-[11px]">
        <svg viewBox="0 0 24 24" class="size-5" fill="currentColor" aria-hidden="true">
            <path d="M7 7V6a5 5 0 0 1 10 0v1h3.2l-1.7 10.2A2 2 0 0 1 16.52 19H7.48a2 2 0 0 1-1.98-1.8L3.8 7zm2 0h6V6a3 3 0 0 0-6 0z" />
        </svg>
        {{ __('shop.nav.shop') }}
    </a>
    @auth
        <a href="{{ route('dashboard') }}" class="flex h-16 flex-col items-center justify-center gap-1 text-[11px]">
    @else
        <a href="{{ route('login') }}" class="flex h-16 flex-col items-center justify-center gap-1 text-[11px]">
    @endauth
        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="12" cy="8" r="3" />
            <path d="M5 19c1.4-3 3.9-4.5 7-4.5S17.6 16 19 19" />
        </svg>
        {{ __('shop.nav.account') }}
    </a>
</nav>
