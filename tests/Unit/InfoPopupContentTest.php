<?php

namespace Tests\Unit;

use App\Support\InfoPopupContent;
use PHPUnit\Framework\TestCase;

class InfoPopupContentTest extends TestCase
{
    public function test_legacy_text_and_youtube_link_become_blocks(): void
    {
        $blocks = InfoPopupContent::blocksFromLegacy(
            "Linia 1\nLinia 2",
            'https://youtu.be/dQw4w9WgXcQ',
        );

        $this->assertSame(['paragraph', 'video'], array_column($blocks, 'type'));
        $this->assertStringContainsString('Linia 1', $blocks[0]['data']['text']);
        $this->assertStringContainsString('Linia 2', $blocks[0]['data']['text']);
        $this->assertStringContainsString('<br', $blocks[0]['data']['text']);
        $this->assertSame('https://youtu.be/dQw4w9WgXcQ', $blocks[1]['data']['url']);
        $this->assertSame(
            'https://www.youtube.com/embed/dQw4w9WgXcQ',
            InfoPopupContent::embedUrl($blocks[1]['data']['url']),
        );
    }

    public function test_legacy_direct_video_file_is_kept_as_a_link(): void
    {
        $blocks = InfoPopupContent::blocksFromLegacy(
            'Măsurați.',
            'https://cdn.example.com/clip.mp4',
        );

        $this->assertSame('paragraph', $blocks[0]['type']);
        $this->assertStringContainsString('Măsurați.', $blocks[0]['data']['text']);
        $this->assertSame('paragraph', $blocks[1]['type']);
        $this->assertStringContainsString('https://cdn.example.com/clip.mp4', $blocks[1]['data']['text']);
        $this->assertStringNotContainsString('iframe', $blocks[1]['data']['text']);
    }

    public function test_unsafe_blocks_are_removed(): void
    {
        $blocks = InfoPopupContent::normalize([
            [
                'type' => 'paragraph',
                'data' => ['text' => '<p onclick="alert(1)">Text <strong>sigur</strong></p><script>alert(1)</script><iframe src="https://evil.test"></iframe><img src="data:image/png;base64,aaaa">'],
            ],
            [
                'type' => 'image',
                'data' => ['path' => 'data:image/png;base64,aaaa', 'alt' => 'Cadru'],
            ],
            [
                'type' => 'image',
                'data' => ['path' => 'info-popups/../.env.jpg', 'alt' => 'Secret'],
            ],
            [
                'type' => 'image',
                'data' => ['path' => 'info-popups/cadru.webp', 'alt' => 'Rama'],
            ],
            [
                'type' => 'video',
                'data' => ['url' => '<iframe src="https://evil.test/embed"></iframe>'],
            ],
            [
                'type' => 'video',
                'data' => ['url' => 'https://vimeo.com/123456'],
            ],
            [
                'type' => 'local_video',
                'data' => ['path' => 'info-popups/../secret.mp4'],
            ],
            [
                'type' => 'local_video',
                'data' => ['path' => 'info-popups/clip.exe'],
            ],
            [
                'type' => 'local_video',
                'data' => ['path' => 'info-popups/montaj.mp4'],
            ],
            [
                'type' => 'list',
                'data' => ['style' => 'unordered', 'items' => ['<script>alert(1)</script>Pas', '  ']],
            ],
            [
                'type' => 'html',
                'data' => ['html' => '<script>alert(1)</script>'],
            ],
        ]);

        $this->assertSame(['paragraph', 'image', 'video', 'local_video', 'list'], array_column($blocks, 'type'));
        $this->assertStringContainsString('<strong>sigur</strong>', $blocks[0]['data']['text']);
        $this->assertStringNotContainsString('script', strtolower($blocks[0]['data']['text']));
        $this->assertStringNotContainsString('iframe', strtolower($blocks[0]['data']['text']));
        $this->assertStringNotContainsString('data:image', $blocks[0]['data']['text']);
        $this->assertStringNotContainsString('onclick', $blocks[0]['data']['text']);
        $this->assertSame('info-popups/cadru.webp', $blocks[1]['data']['path']);
        $this->assertSame('https://player.vimeo.com/video/123456', InfoPopupContent::embedUrl($blocks[2]['data']['url']));
        $this->assertSame('info-popups/montaj.mp4', $blocks[3]['data']['path']);
        $this->assertSame('video/mp4', InfoPopupContent::videoMime($blocks[3]['data']['path']));
        $this->assertSame('video/webm', InfoPopupContent::videoMime('info-popups/montaj.webm'));
        $this->assertNull(InfoPopupContent::videoFileUrl('https://cdn.example.com/clip.mp4'));
        $this->assertSame(['alert(1)Pas'], $blocks[4]['data']['items']);
        $this->assertStringNotContainsString('<', $blocks[4]['data']['items'][0]);
    }
}
