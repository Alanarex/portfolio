<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Contact\Http\Controllers\ContactController;

Route::post('/{locale}/contact', ContactController::class)
    ->where(['locale' => 'fr|en'])
    ->middleware('throttle:public-contact')
    ->name('portfolio.contact.submit');
