<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::get('/ready', function () {
    DB::select('select 1');
    Redis::connection()->ping();

    return response()->json(['status' => 'ready']);
})->name('health.readiness');
