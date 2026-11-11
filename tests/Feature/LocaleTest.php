<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_locale_switch_to_russian(): void
    {
        $response = $this->get(route("locale.switch", "ru"));

        $response->assertRedirect();
        $response->assertSessionHas('locale', "ru");
    }

    public function test_locale_switch_to_english(): void
    {
        $response = $this->get(route("locale.switch", "en"));

        $response->assertRedirect();
        $response->assertSessionHas('locale', "en");
    }

    public function test_locale_switch_to_uzbek(): void
    {
        $response = $this->get(route("locale.switch", "uz"));

        $response->assertRedirect();
        $response->assertSessionHas('locale', "uz");
    }

    public function test_invalid_locale_returns_404(): void
    {
        $response = $this->get(route("locale.switch", "fr"));

        $response->assertNotFound();
    }
}