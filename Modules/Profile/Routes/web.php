<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\Admin\CvVersionController;
use Modules\Profile\Http\Controllers\Admin\PortraitController;
use Modules\Profile\Http\Controllers\Admin\ProfileController;
use Modules\Profile\Http\Controllers\PublicCvController;
use Modules\Profile\Http\Controllers\PublicPortraitController;

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/portrait', [PortraitController::class, 'show'])->name('profile.portrait.show');
    Route::post('/profile/portrait', [PortraitController::class, 'store'])->name('profile.portrait.store');
    Route::delete('/profile/portrait', [PortraitController::class, 'destroy'])->name('profile.portrait.destroy');
    Route::post('/cv-versions', [CvVersionController::class, 'store'])->name('cv-versions.store');
    Route::put('/cv-versions/{cvVersion}', [CvVersionController::class, 'update'])->name('cv-versions.update');
    Route::delete('/cv-versions/{cvVersion}', [CvVersionController::class, 'destroy'])->name('cv-versions.destroy');
});

Route::get('/{locale}/cv', PublicCvController::class)
    ->where(['locale' => 'fr|en'])
    ->name('portfolio.cv.download');

Route::get('/media/portrait/{version}', PublicPortraitController::class)
    ->where(['version' => '[a-f0-9]{16}'])
    ->name('portfolio.portrait');
