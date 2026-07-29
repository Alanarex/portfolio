<?php

declare(strict_types=1);

namespace Modules\Portfolio\Contracts;

interface PublicAnalytics
{
    public function pageViewed(string $routeName, string $locale): void;
}
