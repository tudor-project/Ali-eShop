@props([
    'title' => null,
    'description' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ filled($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

        @if (filled($description))
            <meta name="description" content="{{ $description }}">
        @endif

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxScripts

        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="min-h-screen bg-white text-neutral-950 antialiased">
        <div class="flex min-h-screen flex-col pb-16 md:pb-0">
            <x-shop.header />

            <main class="flex-1">
                {{ $slot }}
            </main>

            <x-shop.footer />
        </div>

        <x-shop.mobile-nav />
    </body>
</html>
