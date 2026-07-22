<?php

declare(strict_types=1);

namespace Modules\Settings\Infrastructure;

use Illuminate\Support\Facades\Cache;

final class SettingsCache
{
    public const LOCALES = ['fr', 'en', 'ar'];

    public static function key(string $locale): string
    {
        return "public-settings:{$locale}:v1";
    }

    public static function invalidate(): void
    {
        foreach (self::LOCALES as $locale) {
            Cache::forget(self::key($locale));
        }
    }
}
