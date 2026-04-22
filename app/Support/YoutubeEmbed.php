<?php

namespace App\Support;

final class YoutubeEmbed
{
    /**
     * @return array{0: string, 1: string}|null [embed src, video id]
     */
    public static function parse(?string $url): ?array
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);

        if (preg_match('~(?:youtube\.com/embed/)([a-zA-Z0-9_-]{6,})~', $url, $m)) {
            return ['https://www.youtube.com/embed/'.$m[1], $m[1]];
        }

        if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/shorts/)([a-zA-Z0-9_-]{6,})~', $url, $m)) {
            $id = $m[1];

            return ['https://www.youtube.com/embed/'.$id, $id];
        }

        return null;
    }
}
