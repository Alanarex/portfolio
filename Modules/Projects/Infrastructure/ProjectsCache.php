<?php

declare(strict_types=1);

namespace Modules\Projects\Infrastructure;

use Illuminate\Support\Facades\Cache;

final class ProjectsCache
{
    private const VERSION_KEY = 'public:projects:version';

    public static function key(string $locale, string $scope): string
    {
        return sprintf('public:projects:v%d:%s:%s', self::version(), $locale, $scope);
    }

    public static function invalidate(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return max(1, (int) Cache::get(self::VERSION_KEY, 1));
    }
}
