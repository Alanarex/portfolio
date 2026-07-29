<?php

declare(strict_types=1);

namespace Modules\Portfolio\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Portfolio\Contracts\PublicAnalytics;
use Modules\Portfolio\Infrastructure\NullPublicAnalytics;

final class PortfolioServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PublicAnalytics::class, NullPublicAnalytics::class);
    }
}
