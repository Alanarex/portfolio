<?php

declare(strict_types=1);

namespace Modules\Posts\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Posts\Contracts\PublicPostsReader;
use Modules\Posts\Infrastructure\DatabasePublicPostsReader;
use Modules\Posts\Models\Post;
use Modules\Posts\Policies\PostPolicy;

final class PostsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicPostsReader::class, DatabasePublicPostsReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(Post::class, PostPolicy::class);
    }
}
