@props(['image', 'alt' => ''])

<img
    src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk ?: 'public')->url($image->path) }}"
    alt="{{ $image->alt ?: $alt }}"
    width="800"
    height="800"
    {{ $attributes }}
>
