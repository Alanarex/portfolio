<?php

declare(strict_types=1);

namespace Modules\Career\Infrastructure;

use Illuminate\Support\Facades\Cache;

final class CareerCache
{
    public static function key(string $locale): string
    {
        return "public:career:v1:{$locale}";
    }

    public static function invalidate(): void
    {
        foreach (['fr', 'en'] as $locale) {
            Cache::forget(self::key($locale));
        }
    }
}
