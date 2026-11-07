<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\GitHubActivity\Console\SyncGitHubActivityCommand;
use Modules\GitHubActivity\Contracts\PublicGitHubActivityReader;
use Modules\GitHubActivity\Infrastructure\DatabasePublicGitHubActivityReader;

final class GitHubActivityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicGitHubActivityReader::class, DatabasePublicGitHubActivityReader::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([SyncGitHubActivityCommand::class]);
        }
    }
}
