<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssetHelperTest extends TestCase
{
    public function test_app_storage_asset_returns_null_for_empty_path(): void
    {
        $this->assertNull(app_storage_asset(null));
        $this->assertNull(app_storage_asset(''));
        $this->assertNull(app_storage_asset('   '));
    }

    public function test_app_storage_asset_preserves_absolute_http_urls(): void
    {
        $url = 'https://cdn.example.com/teachers/photo.jpg';

        $this->assertSame($url, app_storage_asset($url));
    }

    public function test_app_storage_asset_strips_duplicate_storage_prefix(): void
    {
        Storage::fake('public');

        $expected = Storage::disk('public')->url('teachers/avatar.jpg');

        $this->assertSame($expected, app_storage_asset('storage/teachers/avatar.jpg'));
        $this->assertSame($expected, app_storage_asset('storage/storage/teachers/avatar.jpg'));
    }

    public function test_app_storage_asset_normalizes_backslashes(): void
    {
        Storage::fake('public');

        $expected = Storage::disk('public')->url('teachers/avatar.jpg');

        $this->assertSame($expected, app_storage_asset('teachers\\avatar.jpg'));
    }

    public function test_app_public_asset_returns_root_relative_path_in_console(): void
    {
        $this->assertSame('/temp/css/style.css', app_public_asset('temp/css/style.css'));
        $this->assertSame('/temp/css/style.css', app_public_asset('/temp/css/style.css'));
    }

    public function test_app_asset_version_uses_configured_version_outside_local(): void
    {
        app()->detectEnvironment(fn () => 'production');
        config(['app.asset_version' => '20260620']);

        $this->assertSame('20260620', app_asset_version('temp/css/style.css'));
        $this->assertSame('20260620', app_asset_version('temp/js/public-layout.js'));
    }

    public function test_app_asset_version_falls_back_to_file_mtime_in_local(): void
    {
        app()->detectEnvironment(fn () => 'local');
        config(['app.asset_version' => null]);

        $path = 'temp/js/theme-init.js';
        $fullPath = public_path($path);

        $this->assertFileExists($fullPath);
        $this->assertSame((string) filemtime($fullPath), app_asset_version($path));
    }
}
