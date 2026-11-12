<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::view('/', 'home')->name('home');
Route::view('/mentions-legales', 'legal')->name('legal');

Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\nDisallow: /login\nDisallow: /dashboard\n\nSitemap: ".url('/sitemap.xml')."\n",
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8'],
))->name('robots');

Route::get('/sitemap.xml', fn () => response(
    view('sitemap', ['urls' => [route('home'), route('legal')]]),
    200,
    ['Content-Type' => 'application/xml; charset=UTF-8'],
))->name('sitemap');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', fn () => Inertia::render('Dashboard', [
        'projects' => [],
    ]))->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
