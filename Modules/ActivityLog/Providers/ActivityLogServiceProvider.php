<?php

declare(strict_types=1);

namespace Modules\ActivityLog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\ActivityLog\Infrastructure\DatabaseAuditRecorder;

final class ActivityLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditRecorder::class, DatabaseAuditRecorder::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }
}
