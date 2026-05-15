<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class MailRuntimeConfigurationTest extends TestCase
{
    public function test_app_service_provider_prefers_resend_mailer_when_api_key_is_present(): void
    {
        config([
            'mail.default' => 'smtp',
            'resend.api_key' => 're_test_runtime_key',
            'services.resend.key' => null,
        ]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('resend', config('mail.default'));
    }

    public function test_app_service_provider_normalizes_smtp_local_domain_from_full_url(): void
    {
        config([
            'mail.mailers.smtp.local_domain' => 'https://eight1-maktab.onrender.com',
            'app.url' => 'https://eight1-maktab.onrender.com',
        ]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('eight1-maktab.onrender.com', config('mail.mailers.smtp.local_domain'));
    }

    public function test_app_service_provider_keeps_smtp_when_resend_key_is_placeholder(): void
    {
        config([
            'mail.default' => 'smtp',
            'resend.api_key' => 're_sizning_kalitingiz',
            'services.resend.key' => null,
        ]);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('smtp', config('mail.default'));
    }

    public function test_mail_configuration_defines_resend_mailer(): void
    {
        $this->assertSame('resend', config('mail.mailers.resend.transport'));
    }
}
