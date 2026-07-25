<?php

declare(strict_types=1);

namespace Modules\Career\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Career\Contracts\PublicCareerReader;
use Modules\Career\Infrastructure\DatabasePublicCareerReader;
use Modules\Career\Models\Experience;
use Modules\Career\Policies\CareerContentPolicy;

final class CareerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicCareerReader::class, DatabasePublicCareerReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(Experience::class, CareerContentPolicy::class);
    }
}
