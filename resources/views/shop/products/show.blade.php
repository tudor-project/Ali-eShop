@php
    $translation = $product->translations->first();
    $category = $product->categories->first();
    $categoryTranslation = $category?->translations->first();
    $gallery = $product->images->whereNull('product_variant_id')->values();

    if ($gallery->isEmpty()) {
        $gallery = $product->images->values();
    }

    $price = \App\Support\Money::format($product->listing_price);
    $variant = $product->variants->first();
    $inStock = $product->variants->contains(function ($variant): bool {
        return $variant->stock_policy === \App\Enums\StockPolicy::Ignore || $variant->stock_quantity > 0;
    });
    $optionGroups = $product->optionGroups->filter(fn ($group) => $group->values->isNotEmpty())->values();
    $hasRequiredOption = $optionGroups->contains(fn ($group): bool => $group->min_selections >= 1);
    $hasOptionInfo = $optionGroups->contains(function ($group): bool {
        $translation = $group->infoPopup?->translations->first();

        return filled($translation?->title) || filled($translation?->body);
    });

    $videoEmbed = function (?string $url): ?string {
        if ($url === null || ! preg_match('#^https?://#i', $url)) {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]{11})~', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    };
@endphp

<x-shop.layout
    :title="$translation->seo_title ?: $translation->name"
    :description="$translation->seo_description ?: $translation->short_description"
>
    <article class="px-4 py-8 sm:px-8 lg:px-12 lg:py-12">
        <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
            <div x-data="{ active: 0 }">
                @if ($gallery->isEmpty())
                    <x-shop.placeholder class="aspect-square w-full" />
                @else
                    <div class="lg:grid lg:grid-cols-[4.5rem_1fr] lg:gap-4">
                        <ol class="mb-4 hidden gap-3 lg:flex lg:flex-col" aria-label="{{ __('shop.products.gallery') }}">
                            @foreach ($gallery as $image)
                                <li>
                                    <button
                                        type="button"
                                        class="block size-11 overflow-hidden border bg-neutral-50 lg:size-16"
                                        x-bind:class="active === {{ $loop->index }} ? 'border-neutral-900' : 'border-transparent'"
                                        x-on:click="active = {{ $loop->index }}"
                                    >
                                        <span class="sr-only">{{ __('shop.products.thumbnail', ['number' => $loop->iteration]) }}</span>
                                        <x-shop.image :image="$image" :alt="$translation->name" class="h-full w-full object-contain" loading="lazy" />
                                    </button>
                                </li>
                            @endforeach
                        </ol>

                        <div class="relative aspect-square overflow-hidden bg-neutral-50">
                            @if ($product->is_featured)
                                <span class="absolute top-4 left-4 z-10 rounded-full bg-orange-400 px-2.5 py-1 text-[11px] font-medium tracking-wide text-white uppercase">
                                    {{ __('shop.products.featured') }}
                                </span>
                            @endif

                            @foreach ($gallery as $image)
                                <x-shop.image
                                    :image="$image"
                                    :alt="$translation->name"
                                    class="absolute inset-0 h-full w-full object-contain"
                                    @unless ($loop->first) hidden @endunless
                                    x-bind:hidden="active !== {{ $loop->index }}"
                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                />
                            @endforeach
                        </div>
                    </div>

                    @if ($gallery->count() > 1)
                        <div class="mt-4 flex justify-center gap-2 lg:hidden" aria-label="{{ __('shop.products.gallery') }}">
                            @foreach ($gallery as $image)
                                <button
                                    type="button"
                                    class="size-11"
                                    x-on:click="active = {{ $loop->index }}"
                                >
                                    <span class="sr-only">{{ __('shop.products.thumbnail', ['number' => $loop->iteration]) }}</span>
                                    <span
                                        class="mx-auto block size-2 rounded-full"
                                        x-bind:class="active === {{ $loop->index }} ? 'bg-neutral-900' : 'bg-neutral-300'"
                                    ></span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            <div>
                <nav class="text-xs text-neutral-400" aria-label="{{ __('shop.products.breadcrumb') }}">
                    <a href="{{ route('home') }}" class="hover:text-neutral-700">{{ __('shop.nav.home') }}</a>
                    <span aria-hidden="true"> / </span>
                    <a href="{{ route('products.index') }}" class="hover:text-neutral-700">{{ __('shop.nav.shop') }}</a>
                    @if ($categoryTranslation)
                        <span aria-hidden="true"> / </span>
                        <a href="{{ route('products.index', ['category' => $categoryTranslation->slug]) }}" class="hover:text-neutral-700">
                            {{ $categoryTranslation->name }}
                        </a>
                    @endif
                    <span aria-hidden="true"> / </span>
                    <span class="text-neutral-500">{{ $translation->name }}</span>
                </nav>

                <h1 class="mt-8 text-3xl font-medium tracking-tight">{{ $translation->name }}</h1>

                @if (filled($translation->short_description))
                    <p class="mt-4 max-w-xl text-sm leading-6 text-neutral-600">{{ $translation->short_description }}</p>
                @endif

                @if ($price)
                    <p class="mt-6 text-2xl">{{ $price }}</p>
                @endif

                <form class="mt-8 max-w-xl" x-data="{ quantity: 1, copied: false, info: null }">
                    @if ($optionGroups->isNotEmpty())
                        @if ($hasRequiredOption)
                            <p class="text-sm text-neutral-700">{{ __('shop.products.required_hint') }}</p>
                        @endif
                        @if ($hasOptionInfo)
                            <p @class(['text-sm text-neutral-600', 'mt-1' => $hasRequiredOption])>{{ __('shop.products.option_info_hint') }}</p>
                        @endif

                        <div @class(['rounded-lg bg-neutral-100 px-4 sm:px-5', 'mt-4' => $hasRequiredOption || $hasOptionInfo])>
                            @foreach ($optionGroups as $group)
                                @php
                                    $optionName = $group->translations->first()?->name;
                                    $popupTranslation = $group->infoPopup?->translations->first();
                                    $showInfo = filled($popupTranslation?->title) || filled($popupTranslation?->body);
                                @endphp
                                <div class="flex flex-col gap-2 border-b border-neutral-200/80 py-3 last:border-b-0 sm:flex-row sm:items-center sm:justify-between sm:gap-6">
                                    <div class="flex min-w-0 items-center gap-1">
                                        @if ($showInfo)
                                            <button
                                                type="button"
                                                class="inline-flex size-11 shrink-0 items-center justify-center"
                                                x-on:click="info = {{ $group->id }}"
                                                aria-haspopup="dialog"
                                                x-bind:aria-expanded="(info === {{ $group->id }}).toString()"
                                                aria-label="{{ __('shop.products.option_info', ['option' => $optionName]) }}"
                                            >
                                                <span class="inline-flex size-5 items-center justify-center rounded-full bg-teal-700 text-[11px] font-semibold text-white" aria-hidden="true">i</span>
                                            </button>
                                        @endif
                                        <span class="text-sm whitespace-nowrap">
                                            {{ $optionName }}
                                            @if ($group->min_selections >= 1)
                                                <span aria-hidden="true">*</span>
                                                <span class="sr-only">{{ __('shop.products.required') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <select name="options[{{ $group->id }}]" class="h-11 w-full border border-neutral-300 bg-white px-3 outline-none sm:max-w-xs" aria-label="{{ $optionName }}">
                                        <option value="">{{ __('shop.products.choose_option') }}</option>
                                        @foreach ($group->values as $value)
                                            <option value="{{ $value->id }}">{{ $value->translations->first()?->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                        </div>
                        <button
                            type="button"
                            class="mt-3 inline-flex h-11 items-center text-xs tracking-[0.14em] text-neutral-500 uppercase"
                            x-on:click="$root.querySelectorAll('select').forEach((select) => { select.value = '' })"
                        >
                            {{ __('shop.products.clear') }}
                        </button>

                        <div
                            class="fixed inset-0 z-50 bg-white md:flex md:items-center md:justify-center md:bg-black/40 md:p-6"
                            x-cloak
                            x-show="info !== null"
                            x-on:click.self="info = null"
                            x-on:keydown.escape.window="info = null"
                        >
                            <div
                                class="flex h-full w-full flex-col bg-white md:h-auto md:max-h-[80vh] md:max-w-lg"
                                role="dialog"
                                aria-modal="true"
                                x-bind:aria-labelledby="info ? 'option-info-title-' + info : null"
                            >
                                <div class="flex justify-end">
                                    <button type="button" class="inline-flex size-11 items-center justify-center" x-on:click="info = null">
                                        <span class="sr-only">{{ __('shop.nav.close') }}</span>
                                        <svg viewBox="0 0 24 24" class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path d="M6 6l12 12M18 6 6 18" />
                                        </svg>
                                    </button>
                                </div>
                                <div class="overflow-y-auto px-6 pb-8">
                                    @foreach ($optionGroups as $group)
                                        @php
                                            $popup = $group->infoPopup;
                                            $popupTranslation = $popup?->translations->first();
                                        @endphp
                                        @continue(! filled($popupTranslation?->title) && ! filled($popupTranslation?->body))
                                        <div x-show="info === {{ $group->id }}">
                                            <h2 id="option-info-title-{{ $group->id }}" class="text-lg font-medium">{{ $popupTranslation->title }}</h2>
                                            @if (filled($popupTranslation->body))
                                                <div class="mt-4 text-sm leading-7 whitespace-pre-line text-neutral-600">{{ $popupTranslation->body }}</div>
                                            @endif
                                            @php
                                                $embedUrl = $videoEmbed($popup->video_url);
                                                $fileUrl = $embedUrl === null && is_string($popup->video_url) && preg_match('#^https?://#i', $popup->video_url)
                                                    ? $popup->video_url
                                                    : null;
                                            @endphp
                                            @if ($embedUrl || $fileUrl)
                                                <template x-if="info === {{ $group->id }}">
                                                    <div class="mt-6">
                                                        @if ($embedUrl)
                                                            <iframe class="aspect-video w-full" src="{{ $embedUrl }}" title="{{ $popupTranslation->title }}" allowfullscreen></iframe>
                                                        @else
                                                            <video class="w-full" controls playsinline src="{{ $fileUrl }}"></video>
                                                        @endif
                                                    </div>
                                                </template>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                        <div class="flex h-12 w-full items-center justify-between border border-neutral-300 px-1 sm:w-36">
                            <button
                                type="button"
                                class="inline-flex size-11 items-center justify-center"
                                x-on:click="quantity = Math.max(1, quantity - 1)"
                            >
                                <span class="sr-only">{{ __('shop.products.decrease') }}</span>
                                <span aria-hidden="true">−</span>
                            </button>
                            <input
                                type="number"
                                name="quantity"
                                min="1"
                                x-model.number="quantity"
                                class="w-10 border-0 bg-transparent text-center outline-none [appearance:textfield]"
                                aria-label="{{ __('shop.products.quantity') }}"
                            >
                            <button
                                type="button"
                                class="inline-flex size-11 items-center justify-center"
                                x-on:click="quantity = quantity + 1"
                            >
                                <span class="sr-only">{{ __('shop.products.increase') }}</span>
                                <span aria-hidden="true">+</span>
                            </button>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-12 flex-1 items-center justify-center bg-neutral-200 text-xs font-medium tracking-[0.16em] uppercase disabled:text-neutral-400"
                            @disabled(! $inStock)
                        >
                            {{ $inStock ? __('shop.products.add_to_cart') : __('shop.products.out_of_stock') }}
                        </button>
                    </div>

                    <button
                        type="button"
                        class="mt-6 inline-flex h-11 items-center gap-2 text-xs font-medium tracking-[0.14em] uppercase"
                        x-on:click="navigator.share ? navigator.share({ title: @js($translation->name), url: window.location.href }) : navigator.clipboard.writeText(window.location.href).then(() => { copied = true })"
                    >
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <circle cx="6" cy="12" r="2" />
                            <circle cx="17" cy="7" r="2" />
                            <circle cx="17" cy="17" r="2" />
                            <path d="m8 11 7-3M8 13l7 3" />
                        </svg>
                        <span x-text="copied ? @js(__('shop.products.copied')) : @js(__('shop.products.share'))"></span>
                    </button>
                </form>

                <dl class="mt-8 grid max-w-xs grid-cols-[auto_1fr] gap-x-6 gap-y-2 text-xs tracking-[0.14em] uppercase">
                    @if ($variant)
                        <dt class="text-neutral-400">{{ __('shop.products.sku') }}</dt>
                        <dd>{{ $variant->sku }}</dd>
                    @endif
                    @if ($categoryTranslation)
                        <dt class="text-neutral-400">{{ __('shop.products.category') }}</dt>
                        <dd>
                            <a href="{{ route('products.index', ['category' => $categoryTranslation->slug]) }}" class="hover:underline">
                                {{ $categoryTranslation->name }}
                            </a>
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        @if (filled($translation->description))
            <section class="mt-16 border-t border-neutral-200 pt-10 lg:grid lg:grid-cols-[16rem_1fr] lg:items-start lg:pt-16">
                <h2 class="relative text-sm text-neutral-950">
                    <span class="hidden lg:absolute lg:top-1/2 lg:left-0 lg:block lg:w-5 lg:-translate-y-1/2 lg:border-b-2 lg:border-neutral-950" aria-hidden="true"></span>
                    <span class="inline-block border-b-2 border-neutral-950 pb-1 tracking-wide uppercase lg:translate-x-8 lg:border-0 lg:pb-0 lg:normal-case">
                        {{ __('shop.products.description') }}
                    </span>
                </h2>
                <div class="mt-8 text-sm leading-7 whitespace-pre-line text-neutral-500 lg:mt-0 lg:border-l lg:border-neutral-200 lg:pl-16">{{ $translation->description }}</div>
            </section>
        @endif
    </article>
</x-shop.layout>
