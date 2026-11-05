<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Posts\Http\Controllers\Admin\PostsController;

Route::middleware(['auth', 'admin'])->prefix('dashboard')->name('dashboard.')->group(function (): void {
    Route::get('/posts', [PostsController::class, 'index'])->name('posts.index');
    Route::post('/posts', [PostsController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostsController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostsController::class, 'destroy'])->name('posts.destroy');
});
