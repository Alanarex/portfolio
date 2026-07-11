<?php

declare(strict_types=1);

namespace Modules\Foundation\Providers;

use Illuminate\Support\ServiceProvider;

final class FoundationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/health.php');
    }
}
