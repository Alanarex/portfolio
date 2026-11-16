<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AccountSessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardHomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('login.store');
});

require base_path('Modules/Profile/Routes/web.php');
require base_path('Modules/Settings/Routes/web.php');
require base_path('Modules/Career/Routes/web.php');
require base_path('Modules/Skills/Routes/web.php');
require base_path('Modules/Projects/Routes/web.php');
require base_path('Modules/Posts/Routes/web.php');
require base_path('Modules/Portfolio/Routes/web.php');
require base_path('Modules/Portfolio3D/Routes/web.php');
require base_path('Modules/Contact/Routes/web.php');

Route::middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/dashboard', DashboardHomeController::class)->name('dashboard');
    Route::middleware('admin.locale')->prefix('dashboard/account')->name('dashboard.account.')->group(function (): void {
        Route::get('/', [AccountController::class, 'show'])->name('show');
        Route::put('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
        Route::delete('/sessions', [AccountSessionController::class, 'destroyOthers'])->name('sessions.destroy-others');
        Route::delete('/sessions/{handle}', [AccountSessionController::class, 'destroy'])->where('handle', '[a-f0-9]{32}')->name('sessions.destroy');
    });
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
