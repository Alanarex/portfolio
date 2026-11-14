<?php

declare(strict_types=1);

namespace Modules\Skills\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Skills\Contracts\PublicSkillsReader;
use Modules\Skills\Contracts\SkillCatalog;
use Modules\Skills\Infrastructure\DatabasePublicSkillsReader;
use Modules\Skills\Infrastructure\DatabaseSkillCatalog;
use Modules\Skills\Models\SkillCategory;
use Modules\Skills\Policies\SkillsContentPolicy;

final class SkillsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicSkillsReader::class, DatabasePublicSkillsReader::class);
        $this->app->bind(SkillCatalog::class, DatabaseSkillCatalog::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(SkillCategory::class, SkillsContentPolicy::class);
    }
}
