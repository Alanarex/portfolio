<?php

use App\Http\Controllers\HealthController;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnsureAdministrator;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Outside the web group on purpose: no session, cookie or cache dependency,
            // so /health still answers (503) when Redis or the database is down.
            Route::get('/health', HealthController::class)->name('health');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Production traffic only reaches PHP through Caddy → Nginx on the private Docker network,
        // and Caddy replaces any client-supplied X-Forwarded-* headers.
        $middleware->trustProxies(at: '*');
        $middleware->append(AssignRequestId::class);
        $middleware->web(append: [HandleInertiaRequests::class]);
        $middleware->alias([
            'admin' => EnsureAdministrator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
