<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Portfolio\Http\Controllers\PublicPortfolioController;

Route::permanentRedirect('/', '/fr')->name('portfolio.x-default');

Route::prefix('{locale}')
    ->where(['locale' => 'fr|en'])
    ->group(function (): void {
        Route::get('/', [PublicPortfolioController::class, 'home'])->name('portfolio.home');
        Route::get('/projects', [PublicPortfolioController::class, 'index'])->name('portfolio.projects.index');
        Route::get('/projects/{slug}', [PublicPortfolioController::class, 'show'])
            ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
            ->name('portfolio.projects.show');
    });
