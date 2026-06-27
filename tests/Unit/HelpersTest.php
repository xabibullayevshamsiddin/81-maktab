<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_gmail_compose_url_contains_email(): void
    {
        $url = gmail_compose_url("test@example.com", "Subject", "Body text");

        $this->assertStringContainsString(urlencode("test@example.com"), $url);
        $this->assertStringContainsString(urlencode("Subject"), $url);
    }

    public function test_gmail_compose_url_without_optional(): void
    {
        $url = gmail_compose_url("test@example.com");

        $this->assertStringContainsString(urlencode("test@example.com"), $url);
    }

    public function test_app_public_asset_in_console(): void
    {
        $this->assertSame("/temp/css/style.css", app_public_asset("temp/css/style.css"));
        $this->assertSame("/temp/css/style.css", app_public_asset("/temp/css/style.css"));
    }

    public function test_app_storage_asset_null_for_empty(): void
    {
        $this->assertNull(app_storage_asset(null));
        $this->assertNull(app_storage_asset(""));
        $this->assertNull(app_storage_asset("   "));
    }

    public function test_app_storage_asset_preserves_absolute_urls(): void
    {
        $url = "https://cdn.example.com/photo.jpg";
        $this->assertSame($url, app_storage_asset($url));
    }

    public function test_app_storage_asset_strips_duplicate_prefix(): void
    {
        Storage::fake("public");

        $expected = Storage::disk("public")->url("teachers/avatar.jpg");

        $this->assertSame($expected, app_storage_asset("storage/teachers/avatar.jpg"));
    }

    public function test_app_asset_version_in_production(): void
    {
        app()->detectEnvironment(fn() => "production");
        config(["app.asset_version" => "v1"]);

        $this->assertSame("v1", app_asset_version("temp/css/style.css"));
    }

    public function test_app_asset_version_in_local(): void
    {
        app()->detectEnvironment(fn() => "local");
        config(["app.asset_version" => ""]);

        $path = "temp/js/theme-init.js";
        $fullPath = public_path($path);

        $this->assertFileExists($fullPath);
        $this->assertSame((string) filemtime($fullPath), app_asset_version($path));
    }

    public function test_app_public_base_url_returns_string(): void
    {
        $url = app_public_base_url();

        $this->assertIsString($url);
    }

    public function test_app_asset_version_uses_configured_version(): void
    {
        config(["app.asset_version" => "custom-version"]);

        $this->assertSame("custom-version", app_asset_version("any/file.css"));
    }
}