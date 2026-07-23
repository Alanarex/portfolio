<?php

declare(strict_types=1);

namespace Modules\Settings\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Settings\Contracts\PublicSettingsReader;
use Modules\Settings\Infrastructure\DatabasePublicSettingsReader;
use Modules\Settings\Models\SiteSetting;
use Modules\Settings\Policies\SiteSettingPolicy;

final class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicSettingsReader::class, DatabasePublicSettingsReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(SiteSetting::class, SiteSettingPolicy::class);
    }
}
