<?php

declare(strict_types=1);

namespace Modules\Profile\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Profile\Contracts\PublicProfileReader;
use Modules\Profile\Infrastructure\DatabasePublicProfileReader;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;
use Modules\Profile\Policies\CvVersionPolicy;
use Modules\Profile\Policies\ProfilePolicy;

final class ProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicProfileReader::class, DatabasePublicProfileReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(CvVersion::class, CvVersionPolicy::class);
    }
}
