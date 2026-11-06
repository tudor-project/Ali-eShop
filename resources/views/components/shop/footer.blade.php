<footer class="mt-16 border-t border-neutral-200">
    <div class="grid gap-10 px-4 py-12 sm:px-8 md:grid-cols-3 lg:px-12">
        <div class="max-w-xs">
            <p class="text-sm font-semibold tracking-[0.22em] uppercase">{{ config('app.name') }}</p>
            <p class="mt-4 text-sm leading-6 text-neutral-600">{{ __('shop.footer.blurb') }}</p>
        </div>

        <nav aria-label="{{ __('shop.footer.shop') }}">
            <p class="text-sm text-neutral-500">{{ __('shop.footer.shop') }}</p>
            <ul class="mt-4 space-y-3 text-sm">
                <li><a href="{{ route('home') }}" class="inline-flex min-h-11 items-center">{{ __('shop.nav.home') }}</a></li>
                <li><a href="{{ route('products.index') }}" class="inline-flex min-h-11 items-center">{{ __('shop.nav.shop') }}</a></li>
            </ul>
        </nav>

        <nav aria-label="{{ __('shop.footer.account') }}">
            <p class="text-sm text-neutral-500">{{ __('shop.footer.account') }}</p>
            <ul class="mt-4 space-y-3 text-sm">
                <li>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center">{{ __('shop.nav.account') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center">{{ __('shop.nav.sign_in') }}</a>
                    @endauth
                </li>
            </ul>
        </nav>
    </div>

    <div class="border-t border-neutral-200 px-4 py-6 text-xs text-neutral-500 sm:px-8 lg:px-12">
        © {{ now()->year }} {{ config('app.name') }}. {{ __('shop.footer.rights') }}
    </div>
</footer>
