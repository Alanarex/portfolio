<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Skills\Http\Controllers\Admin\SkillsController;

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/skills', [SkillsController::class, 'edit'])->name('skills.edit');
    Route::put('/skills', [SkillsController::class, 'update'])->name('skills.update');
});
