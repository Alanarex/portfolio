<?php

use App\Providers\AppServiceProvider;
use Modules\ActivityLog\Providers\ActivityLogServiceProvider;
use Modules\Profile\Providers\ProfileServiceProvider;
use Modules\Settings\Providers\SettingsServiceProvider;

return [
    AppServiceProvider::class,
    ActivityLogServiceProvider::class,
    ProfileServiceProvider::class,
    SettingsServiceProvider::class,
];
