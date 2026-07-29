<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Admin\MediaController;
use Modules\Projects\Http\Controllers\Admin\ProjectController;
use Modules\Projects\Http\Controllers\PublicMediaController;

Route::get('/{locale}/media/{deliveryKey}', [PublicMediaController::class, 'show'])
    ->where([
        'locale' => 'fr|en',
        'deliveryKey' => '[0-9a-fA-F-]{36}',
    ])
    ->name('portfolio.media.show');

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/order', [ProjectController::class, 'reorder'])->name('projects.reorder');
    Route::get('/projects/{project}/preview', [ProjectController::class, 'preview'])->name('projects.preview');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::put('/media/{mediaAsset}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/media/{mediaAsset}', [MediaController::class, 'destroy'])->name('media.destroy');
});
