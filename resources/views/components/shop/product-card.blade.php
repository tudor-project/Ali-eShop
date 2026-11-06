@props(['product'])

@php
    $translation = $product->translations->first();
    $image = $product->images->first(fn ($image) => $image->product_variant_id === null && $image->is_primary)
        ?? $product->images->first(fn ($image) => $image->product_variant_id === null)
        ?? $product->images->first();
    $url = route('products.show', $translation->slug);
    $price = \App\Support\Money::format($product->listing_price);
@endphp

<article class="group min-w-0" x-data="{ saved: false }">
    <div class="overflow-hidden bg-white motion-safe:transition motion-safe:duration-300 pointer-fine:group-hover:shadow-[0_8px_32px_rgba(0,0,0,0.1)]">
        <div class="relative aspect-[3/4] bg-neutral-50">
            @if ($product->is_featured)
                <span class="absolute top-3 left-1/2 z-10 -translate-x-1/2 rounded-full bg-orange-400 px-2.5 py-1 text-[11px] font-medium tracking-wide text-white uppercase">
                    {{ __('shop.products.featured') }}
                </span>
            @endif

            <a href="{{ $url }}" class="absolute inset-0" aria-label="{{ $translation->name }}">
                @if ($image)
                    <x-shop.image
                        :image="$image"
                        :alt="$translation->name"
                        class="h-full w-full object-contain"
                        loading="lazy"
                    />
                @else
                    <x-shop.placeholder class="h-full w-full" />
                @endif
            </a>
        </div>

        <div class="relative bg-white px-3 pt-3.5 pb-2 motion-safe:transition motion-safe:duration-300 sm:px-5 pointer-fine:group-hover:-translate-y-10 pointer-fine:group-hover:bg-white">
            <h2 class="text-base font-normal">
                <a href="{{ $url }}" class="flex min-h-11 items-center pointer-fine:min-h-0">{{ $translation->name }}</a>
            </h2>

            @if ($price)
                <p class="text-sm font-medium">{{ $price }}</p>
            @endif

            <div class="mt-1 flex flex-wrap items-center gap-x-1 pointer-fine:absolute pointer-fine:inset-x-5 pointer-fine:top-full pointer-fine:mt-0 pointer-fine:h-10 pointer-fine:flex-nowrap pointer-fine:translate-y-1/2 pointer-fine:opacity-0 pointer-fine:pointer-events-none pointer-fine:transition pointer-fine:duration-300 pointer-fine:group-hover:translate-y-0 pointer-fine:group-hover:opacity-100 pointer-fine:group-hover:pointer-events-auto">
                <a href="{{ $url }}" class="inline-flex min-h-11 items-center text-[11px] font-semibold tracking-[0.06em] uppercase pointer-fine:min-h-0 pointer-fine:shrink-0 pointer-fine:whitespace-nowrap">
                    {{ __('shop.products.add_to_cart') }}
                </a>

                <span class="ml-auto flex items-center">
                    <a href="{{ $url }}" class="inline-flex size-11 items-center justify-center pointer-fine:size-10" aria-label="{{ __('shop.products.view') }}">
                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6S2 12 2 12z" />
                            <circle cx="12" cy="12" r="2.5" />
                        </svg>
                    </a>
                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center pointer-fine:size-10"
                        x-on:click="saved = !saved"
                        x-bind:aria-pressed="saved.toString()"
                    >
                        <span class="sr-only">{{ __('shop.products.wishlist') }}</span>
                        <svg viewBox="0 0 24 24" class="size-5" stroke="currentColor" stroke-width="1.5" aria-hidden="true" x-bind:fill="saved ? 'currentColor' : 'none'">
                            <path d="M12 20s-7-4.4-7-9a4 4 0 0 1 7-2 4 4 0 0 1 7 2c0 4.6-7 9-7 9z" />
                        </svg>
                    </button>
                </span>
            </div>
        </div>
    </div>
</article>
