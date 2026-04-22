<?php

namespace App\Providers;

use App\Services\ImageService;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        Carbon::setLocale(config('app.locale'));

        SEOMeta::setTitleDefault('81-IDUM');
        SEOMeta::setTitleSeparator(' | ');
        SEOMeta::setDescription('81-sonli ixtisoslashtirilgan davlat umumta\'lim maktabi — yangiliklar, o\'qituvchilar, kurslar, imtihonlar.');
        SEOMeta::setRobots('index, follow');

        OpenGraph::setSiteName('81-IDUM');
        OpenGraph::setType('website');
    }
}
