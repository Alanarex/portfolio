<?php

declare(strict_types=1);

namespace Modules\Projects\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Projects\Contracts\MediaStorage;
use Modules\Projects\Contracts\PublicProjectReader;
use Modules\Projects\Infrastructure\DatabasePublicProjectReader;
use Modules\Projects\Infrastructure\LocalMediaStorage;
use Modules\Projects\Models\MediaAsset;
use Modules\Projects\Models\Project;
use Modules\Projects\Policies\MediaAssetPolicy;
use Modules\Projects\Policies\ProjectPolicy;

final class ProjectsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/projects.php', 'projects');
        $this->app->bind(PublicProjectReader::class, DatabasePublicProjectReader::class);
        $this->app->bind(MediaStorage::class, LocalMediaStorage::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);
    }
}
