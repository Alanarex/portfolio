<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Profile\Http\Controllers\Admin\CvVersionController;
use Modules\Profile\Http\Controllers\Admin\ProfileController;

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/cv-versions', [CvVersionController::class, 'store'])->name('cv-versions.store');
    Route::put('/cv-versions/{cvVersion}', [CvVersionController::class, 'update'])->name('cv-versions.update');
    Route::delete('/cv-versions/{cvVersion}', [CvVersionController::class, 'destroy'])->name('cv-versions.destroy');
});
