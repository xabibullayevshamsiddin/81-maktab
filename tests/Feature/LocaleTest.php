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
        $this->assertSame("ru", app()->getLocale());
    }

    public function test_locale_switch_to_english(): void
    {
        $response = $this->get(route("locale.switch", "en"));

        $response->assertRedirect();
        $this->assertSame("en", app()->getLocale());
    }

    public function test_locale_switch_to_uzbek(): void
    {
        $response = $this->get(route("locale.switch", "uz"));

        $response->assertRedirect();
        $this->assertSame("uz", app()->getLocale());
    }

    public function test_invalid_locale_returns_404(): void
    {
        $response = $this->get(route("locale.switch", "fr"));

        $response->assertNotFound();
    }
}