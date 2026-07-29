<?php

use App\Providers\AppServiceProvider;
use Modules\ActivityLog\Providers\ActivityLogServiceProvider;
use Modules\Career\Providers\CareerServiceProvider;
use Modules\Profile\Providers\ProfileServiceProvider;
use Modules\Projects\Providers\ProjectsServiceProvider;
use Modules\Settings\Providers\SettingsServiceProvider;
use Modules\Skills\Providers\SkillsServiceProvider;

return [
    AppServiceProvider::class,
    ActivityLogServiceProvider::class,
    CareerServiceProvider::class,
    ProfileServiceProvider::class,
    ProjectsServiceProvider::class,
    SettingsServiceProvider::class,
    SkillsServiceProvider::class,
];
