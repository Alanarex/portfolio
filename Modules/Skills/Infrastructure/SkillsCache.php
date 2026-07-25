<?php

declare(strict_types=1);

namespace Modules\Skills\Infrastructure;

use Illuminate\Support\Facades\Cache;

final class SkillsCache
{
    public static function key(string $locale): string
    {
        return "public:skills:v1:{$locale}";
    }

    public static function invalidate(): void
    {
        foreach (['fr', 'en'] as $locale) {
            Cache::forget(self::key($locale));
        }
    }
}
