<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::define('manage-account', static fn (User $user): bool => $user->isAdministrator());

        RateLimiter::for('admin-login', static function (Request $request): Limit {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by(hash('sha256', $email.'|'.$request->ip()));
        });

        RateLimiter::for('public-contact', static function (Request $request): Limit {
            return Limit::perMinute(3)->by(hash('sha256', (string) $request->ip()));
        });
    }
}
