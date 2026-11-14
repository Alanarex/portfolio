<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Portfolio3D\Http\Controllers\Admin\SceneController;
use Modules\Portfolio3D\Http\Controllers\SceneManifestController;

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/scene', [SceneController::class, 'edit'])->name('scene.edit');
    Route::put('/scene', [SceneController::class, 'update'])->name('scene.update');
});

Route::get('/{locale}/scene.json', [SceneManifestController::class, 'show'])
    ->where('locale', 'fr|en')
    ->name('portfolio.scene');
