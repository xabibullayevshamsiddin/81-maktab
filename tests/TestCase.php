<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $appUrl = rtrim((string) config('app.url', 'http://localhost'), '/');

        config(['app.url' => $appUrl]);
        URL::forceRootUrl($appUrl);

        // Testlarda rate limiting ni o'chirish — throttle cache ni tozalash
        $this->flushCache();
    }

    protected function flushCache(): void
    {
        try {
            \Illuminate\Support\Facades\Cache::flush();
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
