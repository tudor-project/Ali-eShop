<?php

namespace App\Support;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerAction;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Throwable;

class InfoPopupContent
{
    /**
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    public static function normalize(mixed $blocks): array
    {
        if (is_string($blocks)) {
            $decoded = json_decode($blocks, true);
            $blocks = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($blocks)) {
            return [];
        }

        $normalized = [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = $block['type'] ?? null;
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];
            $clean = match ($type) {
                'paragraph' => self::paragraphData($data),
                'image' => self::imageData($data),
                'video' => self::videoData($data),
                'local_video' => self::localVideoData($data),
                'list' => self::listData($data),
                default => null,
            };

            if ($clean === null) {
                continue;
            }

            $normalized[] = [
                'type' => $type,
                'data' => $clean,
            ];
        }

        return $normalized;
    }

    /**
     * @return array<int, array{type: string, data: array<string, mixed>}>
     */
    public static function blocksFromLegacy(?string $body, ?string $videoUrl): array
    {
        $blocks = [];

        if (is_string($body) && trim($body) !== '') {
            $blocks[] = [
                'type' => 'paragraph',
                'data' => [
                    'text' => '<p>'.nl2br(e($body), false).'</p>',
                ],
            ];
        }

        if (is_string($videoUrl) && self::embedUrl($videoUrl) !== null) {
            $blocks[] = [
                'type' => 'video',
                'data' => ['url' => trim($videoUrl)],
            ];
        } elseif (self::isPublicHttpUrl($videoUrl)) {
            $url = trim((string) $videoUrl);
            $blocks[] = [
                'type' => 'paragraph',
                'data' => [
                    'text' => '<p><a href="'.e($url).'">'.e($url).'</a></p>',
                ],
            ];
        }

        return self::normalize($blocks);
    }

    public static function embedUrl(?string $url): ?string
    {
        $parts = self::httpParts($url);

        if ($parts === null) {
            return null;
        }

        $host = $parts['host'];
        $path = $parts['path'];

        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
            if (preg_match('~^/(?:embed|shorts)/([\w-]{11})$~', $path, $matches) === 1) {
                return 'https://www.youtube.com/embed/'.$matches[1];
            }

            if ($path === '/watch') {
                parse_str($parts['query'], $query);
                $id = $query['v'] ?? null;

                if (is_string($id) && preg_match('/^[\w-]{11}$/', $id) === 1) {
                    return 'https://www.youtube.com/embed/'.$id;
                }
            }

            return null;
        }

        if ($host === 'youtu.be' && preg_match('/^[\w-]{11}$/', trim($path, '/')) === 1) {
            return 'https://www.youtube.com/embed/'.trim($path, '/');
        }

        if (in_array($host, ['vimeo.com', 'player.vimeo.com'], true) && preg_match('~^(?:/video)?/(\d+)$~', $path, $matches) === 1) {
            return 'https://player.vimeo.com/video/'.$matches[1];
        }

        return null;
    }

    public static function imageUrl(mixed $path): ?string
    {
        $path = self::imagePath($path);

        if ($path === null) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function isStoredImagePath(mixed $path): bool
    {
        return self::imagePath($path) !== null;
    }

    public static function videoFileUrl(mixed $path): ?string
    {
        $path = self::videoPath($path);

        if ($path === null) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public static function videoMime(mixed $path): ?string
    {
        $path = self::videoPath($path);

        if ($path === null) {
            return null;
        }

        return str_ends_with(strtolower($path), '.webm') ? 'video/webm' : 'video/mp4';
    }

    /**
     * @param  array<int, array{type: string, data: array<string, mixed>}>  $blocks
     */
    public static function summary(array $blocks): string
    {
        $lines = [];

        foreach (self::normalize($blocks) as $block) {
            $data = $block['data'];
            $lines[] = match ($block['type']) {
                'paragraph' => __('admin.info_popups.blocks.paragraph'),
                'image' => __('admin.info_popups.blocks.image').': '.($data['alt'] ?? ''),
                'video' => __('admin.info_popups.blocks.video').': '.($data['url'] ?? ''),
                'local_video' => __('admin.info_popups.blocks.local_video').': '.basename((string) ($data['path'] ?? '')),
                'list' => __('admin.info_popups.blocks.list'),
                default => null,
            };
        }

        return implode(' · ', array_filter($lines, fn (?string $line): bool => filled($line)));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{text: string}|null
     */
    private static function paragraphData(array $data): ?array
    {
        $text = $data['text'] ?? null;

        if (is_array($text)) {
            try {
                $text = RichContentRenderer::make($text)->toUnsafeHtml();
            } catch (Throwable) {
                return null;
            }
        }

        if (! is_string($text)) {
            return null;
        }

        $html = self::sanitizeHtml($text);

        if (self::isBlankHtml($html)) {
            return null;
        }

        return ['text' => $html];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{path: string, alt: string}|null
     */
    private static function imageData(array $data): ?array
    {
        $path = self::imagePath($data['path'] ?? null);
        $alt = self::plainText($data['alt'] ?? null, 255);

        if ($path === null || $alt === null) {
            return null;
        }

        return [
            'path' => $path,
            'alt' => $alt,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{url: string}|null
     */
    private static function videoData(array $data): ?array
    {
        $url = $data['url'] ?? null;

        if (! is_string($url) || self::embedUrl($url) === null) {
            return null;
        }

        $url = trim($url);

        if (strlen($url) > 255) {
            return null;
        }

        return ['url' => $url];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{path: string}|null
     */
    private static function localVideoData(array $data): ?array
    {
        $path = self::videoPath($data['path'] ?? null);

        if ($path === null) {
            return null;
        }

        return ['path' => $path];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{style: string, items: array<int, string>}|null
     */
    private static function listData(array $data): ?array
    {
        $style = $data['style'] ?? null;

        if (! in_array($style, ['ordered', 'unordered'], true)) {
            return null;
        }

        $items = [];

        foreach (is_array($data['items'] ?? null) ? $data['items'] : [] as $item) {
            if (is_array($item)) {
                $item = $item['text'] ?? $item['item'] ?? null;
            }

            $text = self::plainText($item, 500);

            if ($text === null) {
                continue;
            }

            $items[] = $text;
        }

        if ($items === []) {
            return null;
        }

        return [
            'style' => $style,
            'items' => $items,
        ];
    }

    private static function imagePath(mixed $path): ?string
    {
        $path = self::relativePath($path);

        if ($path === null || preg_match('#^info-popups/[^/]+\.(?:jpe?g|png|webp|avif)$#iu', $path) !== 1) {
            return null;
        }

        return $path;
    }

    private static function videoPath(mixed $path): ?string
    {
        $path = self::relativePath($path);

        if ($path === null || preg_match('#^info-popups/[^/]+\.(?:mp4|webm)$#iu', $path) !== 1) {
            return null;
        }

        return $path;
    }

    private static function relativePath(mixed $path): ?string
    {
        if (is_array($path)) {
            $path = $path[0] ?? null;
        }

        if (! is_string($path)) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', trim($path)), '/');

        if ($path === '' || str_contains(strtolower($path), 'data:')) {
            return null;
        }

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                return null;
            }
        }

        return $path;
    }

    private static function plainText(mixed $value, int $max): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($value)) ?? '');

        if ($text === '') {
            return null;
        }

        return mb_substr($text, 0, $max);
    }

    private static function sanitizeHtml(string $html): string
    {
        return self::sanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return new HtmlSanitizer(
            (new HtmlSanitizerConfig)
                ->defaultAction(HtmlSanitizerAction::Block)
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('ul')
                ->allowElement('ol')
                ->allowElement('li')
                ->allowElement('a', ['href'])
                ->dropElement('script')
                ->dropElement('style')
                ->dropElement('iframe')
                ->dropElement('object')
                ->dropElement('embed')
                ->dropElement('svg')
                ->dropElement('math')
                ->dropElement('img')
                ->dropElement('video')
                ->dropElement('audio')
                ->dropElement('source')
                ->dropElement('canvas')
                ->dropElement('form')
                ->dropElement('input')
                ->dropElement('button')
                ->dropElement('textarea')
                ->dropElement('select')
                ->dropElement('noscript')
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->forceHttpsUrls()
                ->withMaxInputLength(100000),
        );
    }

    private static function isBlankHtml(string $html): bool
    {
        $text = html_entity_decode(
            strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], ' ', $html)),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8',
        );

        return trim($text) === '';
    }

    private static function isPublicHttpUrl(?string $url): bool
    {
        return self::httpParts($url) !== null;
    }

    /**
     * @return array{host: string, path: string, query: string}|null
     */
    private static function httpParts(?string $url): ?array
    {
        if (! is_string($url)) {
            return null;
        }

        $url = trim($url);

        if ($url === '' || preg_match('/[\x00-\x20\x7F<>"\'\\\\]/', $url) === 1) {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = (string) preg_replace('/^www\./', '', $host);

        if (! in_array($scheme, ['http', 'https'], true) || $host === '' || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        return [
            'host' => $host,
            'path' => (string) ($parts['path'] ?? ''),
            'query' => (string) ($parts['query'] ?? ''),
        ];
    }
}
