<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        try {
            $settings = Setting::pluck('value', 'key');

            if ($host = $settings->get('mail_host')) {
                config()->set('mail.mailers.smtp.host', $host);
            }
            if ($port = $settings->get('mail_port')) {
                config()->set('mail.mailers.smtp.port', (int) $port);
            }
            if ($username = $settings->get('mail_username')) {
                config()->set('mail.mailers.smtp.username', $username);
            }
            if ($password = $settings->get('mail_password')) {
                config()->set('mail.mailers.smtp.password', $password);
            }
            if ($encryption = $settings->get('mail_encryption')) {
                config()->set('mail.mailers.smtp.encryption', $encryption);
            }
            if ($fromAddress = $settings->get('mail_from_address')) {
                config()->set('mail.from.address', $fromAddress);
            }
            if ($fromName = $settings->get('mail_from_name')) {
                config()->set('mail.from.name', $fromName);
            }
            if ($host) {
                config()->set('mail.default', 'smtp');
            }
        } catch (\Exception $e) {
            // settings table may not exist yet
        }
    }
}
