<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Support\Facades\Route;
use Modules\Portfolio\Http\Controllers\LocaleRedirectController;
use Modules\Portfolio\Http\Controllers\PublicPortfolioController;
use Modules\Portfolio\Http\Controllers\PublicSeoController;

// Racine : 302 vers la langue du visiteur (cookie, puis Accept-Language ; robots → /fr, le x-default).
// Sans HandleInertiaRequests : il réécrit l'en-tête Vary (X-Inertia) et masquerait Accept-Language.
Route::get('/', LocaleRedirectController::class)->withoutMiddleware(HandleInertiaRequests::class)->name('portfolio.x-default');
Route::get('/{path}', LocaleRedirectController::class)
    ->where('path', 'projects|privacy|legal|projects/[a-z0-9]+(?:-[a-z0-9]+)*')
    ->withoutMiddleware(HandleInertiaRequests::class)
    ->name('portfolio.unprefixed');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('portfolio.sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('portfolio.robots');

Route::prefix('{locale}')
    ->where(['locale' => 'fr|en'])
    ->group(function (): void {
        Route::get('/', [PublicPortfolioController::class, 'home'])->name('portfolio.home');
        Route::get('/projects', [PublicPortfolioController::class, 'index'])->name('portfolio.projects.index');
        Route::get('/privacy', [PublicPortfolioController::class, 'privacy'])->name('portfolio.privacy');
        Route::get('/legal', [PublicPortfolioController::class, 'legal'])->name('portfolio.legal');
        Route::get('/projects/{slug}', [PublicPortfolioController::class, 'show'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
            ->name('portfolio.projects.show');
    });
