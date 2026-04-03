<?php

namespace App\Providers;

use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ImageService::class, function () {
            return new ImageService;
        });
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));
    }
}
