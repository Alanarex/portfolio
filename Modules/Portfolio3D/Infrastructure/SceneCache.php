<?php

declare(strict_types=1);

namespace Modules\Portfolio3D\Infrastructure;

use Illuminate\Support\Facades\Cache;

final class SceneCache
{
    public const LOCALES = ['fr', 'en'];

    public static function key(string $locale): string
    {
        return "public-scene:{$locale}:v1";
    }

    public static function invalidate(): void
    {
        foreach (self::LOCALES as $locale) {
            Cache::forget(self::key($locale));
        }
    }
}
