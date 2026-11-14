<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Portfolio3D\Contracts\PublicSceneReader;
use Modules\Portfolio3D\Infrastructure\DatabasePublicSceneReader;
use Modules\Portfolio3D\Models\SceneConfiguration;
use Modules\Portfolio3D\Policies\SceneConfigurationPolicy;

final class Portfolio3DServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicSceneReader::class, DatabasePublicSceneReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(SceneConfiguration::class, SceneConfigurationPolicy::class);
    }
}
