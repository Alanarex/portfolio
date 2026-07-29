<?php

declare(strict_types=1);

namespace Modules\Portfolio\Infrastructure;

use Modules\Portfolio\Contracts\PublicAnalytics;

final class NullPublicAnalytics implements PublicAnalytics
{
    public function pageViewed(string $routeName, string $locale): void
    {
        // Privacy default: no vendor, identifier, cookie, network request or persistent event.
    }
}
