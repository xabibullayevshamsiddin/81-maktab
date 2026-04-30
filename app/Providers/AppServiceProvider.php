<?php

namespace App\Providers;

use App\Services\ImageService;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Str;
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
        $this->applyRuntimeMailConfiguration();

        Paginator::useBootstrapFive();

        Carbon::setLocale(config('app.locale'));

        SEOMeta::setTitleDefault('81-IDUM');
        SEOMeta::setTitleSeparator(' | ');
        SEOMeta::setDescription('81-sonli ixtisoslashtirilgan davlat umumta\'lim maktabi — yangiliklar, o\'qituvchilar, kurslar, imtihonlar.');
        SEOMeta::setRobots('index, follow');

        OpenGraph::setSiteName('81-IDUM');
        OpenGraph::setType('website');
    }

    private function applyRuntimeMailConfiguration(): void
    {
        $this->normalizeSmtpLocalDomain();
        $this->preferResendMailerWhenConfigured();
    }

    private function preferResendMailerWhenConfigured(): void
    {
        if (! $this->hasConfiguredResendApiKey()) {
            return;
        }

        $configuredMailer = strtolower(trim((string) config('mail.default', 'smtp')));
        $environmentMailer = strtolower(trim((string) env('MAIL_MAILER', '')));

        if (in_array($configuredMailer, ['smtp', 'resend', ''], true) || $environmentMailer === 'resend') {
            config(['mail.default' => 'resend']);
        }
    }

    private function normalizeSmtpLocalDomain(): void
    {
        $localDomain = $this->normalizeHost((string) config('mail.mailers.smtp.local_domain', ''));

        if ($localDomain === null) {
            $localDomain = $this->normalizeHost((string) config('app.url', ''));
        }

        if ($localDomain !== null) {
            config(['mail.mailers.smtp.local_domain' => $localDomain]);
        }
    }

    private function hasConfiguredResendApiKey(): bool
    {
        $apiKey = trim((string) ($this->resolvedResendApiKey() ?? ''));

        if ($apiKey === '' || ! Str::startsWith($apiKey, 're_')) {
            return false;
        }

        return ! Str::contains(strtolower($apiKey), ['sizning_kalitingiz', 'your_key', 'your-api-key']);
    }

    private function resolvedResendApiKey(): ?string
    {
        $apiKey = config('resend.api_key') ?? config('services.resend.key') ?? env('RESEND_API_KEY');

        return is_string($apiKey) ? trim($apiKey) : null;
    }

    private function normalizeHost(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $prefixedValue = Str::contains($value, '://') ? $value : 'smtp://'.$value;
        $host = parse_url($prefixedValue, PHP_URL_HOST);

        if (! is_string($host) || trim($host) === '') {
            $host = preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $value) ?? '';
            $host = preg_replace('/:\d+$/', '', $host) ?? '';
            $host = trim($host, " \t\n\r\0\x0B/[]");

            if ($host === '' || Str::contains($host, ['/', '?', '#'])) {
                return null;
            }
        }

        return strtolower(trim($host));
    }
}
