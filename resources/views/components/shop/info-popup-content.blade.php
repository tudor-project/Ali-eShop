@props(['blocks' => [], 'title' => ''])

@php
    $blocks = \App\Support\InfoPopupContent::normalize($blocks);
@endphp

@if ($blocks !== [])
    <div class="mt-4 space-y-4 text-sm leading-7 text-neutral-600">
        @foreach ($blocks as $block)
            @switch($block['type'])
                @case('paragraph')
                    <div class="[&_a]:underline [&_h2]:text-base [&_h2]:font-medium [&_h2]:text-neutral-950 [&_h3]:font-medium [&_h3]:text-neutral-950 [&_ol]:list-decimal [&_ol]:ps-5 [&_p+p]:mt-3 [&_ul]:list-disc [&_ul]:ps-5">
                        {!! $block['data']['text'] !!}
                    </div>
                    @break

                @case('image')
                    @php($src = \App\Support\InfoPopupContent::imageUrl($block['data']['path'] ?? null))
                    @if ($src)
                        <img
                            src="{{ $src }}"
                            alt="{{ $block['data']['alt'] }}"
                            width="800"
                            height="800"
                            class="w-full object-contain"
                            loading="lazy"
                        >
                    @endif
                    @break

                @case('video')
                    @php($embed = \App\Support\InfoPopupContent::embedUrl($block['data']['url'] ?? null))
                    @if ($embed)
                        <iframe
                            class="aspect-video w-full"
                            src="{{ $embed }}"
                            title="{{ $title }}"
                            allowfullscreen
                            loading="lazy"
                        ></iframe>
                    @endif
                    @break

                @case('local_video')
                    @php($src = \App\Support\InfoPopupContent::videoFileUrl($block['data']['path'] ?? null))
                    @if ($src)
                        <video class="aspect-video w-full" controls preload="none" playsinline>
                            <source src="{{ $src }}" type="{{ \App\Support\InfoPopupContent::videoMime($block['data']['path'] ?? null) }}">
                        </video>
                    @endif
                    @break

                @case('list')
                    @php($ordered = ($block['data']['style'] ?? '') === 'ordered')
                    @if ($ordered)
                        <ol class="list-outside list-decimal ps-5">
                            @foreach ($block['data']['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <ul class="list-outside list-disc ps-5">
                            @foreach ($block['data']['items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @break
            @endswitch
        @endforeach
    </div>
@endif
