<?php

declare(strict_types=1);

namespace Modules\Profile\Infrastructure;

use Illuminate\Support\Facades\Cache;

final class ProfileCache
{
    public const LOCALES = ['fr', 'en', 'ar'];

    public static function key(string $locale): string
    {
        return "public-profile:{$locale}:v1";
    }

    public static function invalidate(): void
    {
        foreach (self::LOCALES as $locale) {
            Cache::forget(self::key($locale));
        }
    }
}
