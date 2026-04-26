<?php

namespace App\Support;

use Illuminate\Support\Str;

final class VideoEmbed
{
    /**
     * Parse various video URLs into embeddable versions.
     *
     * @param string|null $url
     * @return array{type: string, src: string, id: string|null}|null
     */
    public static function parse(?string $url): ?array
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        // 1. YouTube
        if (preg_match('~(?:youtube\.com/(?:embed/|watch\?v=|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{6,12})~', $url, $m)) {
            $id = $m[1];
            return [
                'type' => 'youtube',
                'src' => "https://www.youtube.com/embed/{$id}?rel=0",
                'id' => $id,
            ];
        }

        // 2. Instagram (Reels, Posts, TV)
        // Pattern: instagram.com/reels/XXXX/ or instagram.com/p/XXXX/ or instagram.com/reel/XXXX/
        if (preg_match('~instagram\.com/(?:p|reel|reels|tv)/([a-zA-Z0-9_-]+)~', $url, $m)) {
            $id = $m[1];
            return [
                'type' => 'instagram',
                'src' => "https://www.instagram.com/p/{$id}/embed",
                'id' => $id,
            ];
        }

        // 3. TikTok
        // Pattern: tiktok.com/@user/video/XXXX or vm.tiktok.com/XXXX
        if (preg_match('~tiktok\.com/.*video/(\d+)~', $url, $m)) {
            $id = $m[1];
            return [
                'type' => 'tiktok',
                'src' => "https://www.tiktok.com/embed/v2/{$id}",
                'id' => $id,
            ];
        }

        return null;
    }
}
