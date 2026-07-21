<?php

use Illuminate\Support\Facades\Route;

Route::view('/{path?}', 'portfolio')->where('path', '.*');
