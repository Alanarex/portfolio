<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');
});

require base_path('Modules/Profile/Routes/web.php');
require base_path('Modules/Settings/Routes/web.php');

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::redirect('/dashboard', '/dashboard/profile')->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::view('/{path?}', 'portfolio')->where('path', '.*')->name('portfolio');
