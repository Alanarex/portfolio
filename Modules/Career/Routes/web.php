<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Career\Http\Controllers\Admin\CareerController;

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/career', [CareerController::class, 'edit'])->name('career.edit');
    Route::put('/career', [CareerController::class, 'update'])->name('career.update');
});
