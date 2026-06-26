<?php

namespace Tests\Unit;

use App\Support\VideoEmbed;
use App\Support\YoutubeEmbed;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    public function test_youtube_url(): void
    {
        $embed = VideoEmbed::parse("https://www.youtube.com/watch?v=dQw4w9WgXcQ");

        $this->assertSame("youtube", $embed["type"]);
        $this->assertSame("dQw4w9WgXcQ", $embed["id"]);
    }

    public function test_youtu_be_url(): void
    {
        $embed = VideoEmbed::parse("https://youtu.be/dQw4w9WgXcQ");

        $this->assertSame("youtube", $embed["type"]);
        $this->assertStringContainsString("dQw4w9WgXcQ", $embed["id"]);
    }

    public function test_instagram_reels(): void
    {
        $embed = VideoEmbed::parse("https://www.instagram.com/reels/ABC123/");

        $this->assertSame("instagram", $embed["type"]);
        $this->assertSame("ABC123", $embed["id"]);
        $this->assertSame("https://www.instagram.com/reel/ABC123/embed", $embed["src"]);
    }

    public function test_instagram_post(): void
    {
        $embed = VideoEmbed::parse("https://www.instagram.com/p/XYZ987/");

        $this->assertSame("instagram", $embed["type"]);
        $this->assertSame("XYZ987", $embed["id"]);
    }

    public function test_unknown_url_returns_null(): void
    {
        $embed = VideoEmbed::parse("https://example.com/video.mp4");

        $this->assertNull($embed);
    }

    public function test_youtube_embed_returns_array(): void
    {
        $result = YoutubeEmbed::parse("https://www.youtube.com/watch?v=abc123def");

        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame("abc123def", $result[1]);
        $this->assertStringContainsString("abc123def", $result[0]);
    }

    public function test_youtube_embed_short_url(): void
    {
        $result = YoutubeEmbed::parse("https://youtu.be/xyz789abc");

        $this->assertNotNull($result);
        $this->assertSame("xyz789abc", $result[1]);
    }

    public function test_youtube_embed_returns_null_for_invalid(): void
    {
        $this->assertNull(YoutubeEmbed::parse(""));
        $this->assertNull(YoutubeEmbed::parse(null));
    }

    public function test_empty_url_returns_null(): void
    {
        $this->assertNull(VideoEmbed::parse(""));
        $this->assertNull(VideoEmbed::parse(null));
    }
}