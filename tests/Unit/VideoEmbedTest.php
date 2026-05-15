<?php

namespace Tests\Unit;

use App\Support\VideoEmbed;
use PHPUnit\Framework\TestCase;

class VideoEmbedTest extends TestCase
{
    public function test_instagram_reels_url_uses_reel_embed_path(): void
    {
        $embed = VideoEmbed::parse('https://www.instagram.com/reels/ABC123_def/');

        $this->assertSame('instagram', $embed['type']);
        $this->assertSame('ABC123_def', $embed['id']);
        $this->assertSame('https://www.instagram.com/reel/ABC123_def/embed', $embed['src']);
    }

    public function test_instagram_post_url_keeps_post_embed_path(): void
    {
        $embed = VideoEmbed::parse('https://www.instagram.com/p/XYZ987/');

        $this->assertSame('instagram', $embed['type']);
        $this->assertSame('XYZ987', $embed['id']);
        $this->assertSame('https://www.instagram.com/p/XYZ987/embed', $embed['src']);
    }
}
