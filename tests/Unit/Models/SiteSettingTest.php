<?php

namespace Tests\Unit\Models;

use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_set_and_get_setting(): void
    {
        SiteSetting::query()->create(["key" => "site_name", "value" => "81-IDUM"]);

        $this->assertSame("81-IDUM", SiteSetting::get("site_name"));
    }

    public function test_get_returns_default(): void
    {
        $this->assertNull(SiteSetting::get("non_existent_key"));
        $this->assertSame("default", SiteSetting::get("non_existent_key", "default"));
    }

    public function test_setting_can_be_updated(): void
    {
        SiteSetting::query()->create(["key" => "maintenance", "value" => "false"]);
        SiteSetting::query()->where("key", "maintenance")->update(["value" => "true"]);

        $this->assertSame("true", SiteSetting::get("maintenance"));
    }

    public function test_toggle_settings(): void
    {
        SiteSetting::query()->create(["key" => "ai_chat_enabled", "value" => "0"]);

        $this->assertSame("0", SiteSetting::get("ai_chat_enabled"));
    }
}