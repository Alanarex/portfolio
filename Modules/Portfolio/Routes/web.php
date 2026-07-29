<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Portfolio\Http\Controllers\PublicPortfolioController;
use Modules\Portfolio\Http\Controllers\PublicSeoController;

Route::permanentRedirect('/', '/fr')->name('portfolio.x-default');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('portfolio.sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('portfolio.robots');

Route::prefix('{locale}')
    ->where(['locale' => 'fr|en'])
    ->group(function (): void {
        Route::get('/', [PublicPortfolioController::class, 'home'])->name('portfolio.home');
        Route::get('/projects', [PublicPortfolioController::class, 'index'])->name('portfolio.projects.index');
        Route::get('/privacy', [PublicPortfolioController::class, 'privacy'])->name('portfolio.privacy');
        Route::get('/projects/{slug}', [PublicPortfolioController::class, 'show'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
            ->name('portfolio.projects.show');
    });
