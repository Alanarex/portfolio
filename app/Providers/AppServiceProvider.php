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

        // Demande de lien : par IP (rafale) et par adresse saisie (une adresse ne peut pas être inondée de courriels).
        RateLimiter::for('password-reset-request', static function (Request $request): array {
            $email = mb_strtolower(trim((string) $request->input('email')));

            return [
                Limit::perMinute(3)->by('ip:'.hash('sha256', (string) $request->ip())),
                Limit::perHour(5)->by('ip-hour:'.hash('sha256', (string) $request->ip())),
                Limit::perHour(3)->by('email:'.hash('sha256', $email)),
            ];
        });

        RateLimiter::for('password-reset', static function (Request $request): array {
            return [
                Limit::perMinute(5)->by('ip:'.hash('sha256', (string) $request->ip())),
                Limit::perHour(20)->by('ip-hour:'.hash('sha256', (string) $request->ip())),
            ];
        });

        // Filet global contre les rafales d'une même IP (hors ressources statiques, servies par Nginx).
        RateLimiter::for('web-global', static function (Request $request): Limit {
            return Limit::perMinute(240)->by(hash('sha256', (string) $request->ip()));
        });

        RateLimiter::for('public-contact', static function (Request $request): array {
            return [
                Limit::perMinute(3)->by(hash('sha256', (string) $request->ip())),
                Limit::perHour(10)->by('h:'.hash('sha256', (string) $request->ip())),
            ];
        });
    }
}
