<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $frontendUrl = rtrim((string) config('knowmycouncil.frontend_url'), '/');
            $email = urlencode((string) $notifiable->getEmailForPasswordReset());

            return "{$frontendUrl}/reset-password/{$token}?email={$email}";
        });
    }
}
