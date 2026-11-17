<?php

declare(strict_types=1);

namespace Modules\Profile\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Profile\Models\Profile;

/** Adresse publique de la photo choisie dans le tableau de bord, ou null pour garder la photo par défaut du site. */
final class PublishedPortrait
{
    public const CACHE_KEY = 'public-profile:portrait:v1';

    public function url(): ?string
    {
        $version = $this->version();

        return $version === null ? null : route('portfolio.portrait', ['version' => $version]);
    }

    public function version(): ?string
    {
        $version = Cache::remember(self::CACHE_KEY, now()->addHour(), function (): string {
            $profile = Profile::query()->where('key', 'main')->where('is_published', true)->first();

            return $profile?->portrait_path !== null && $profile->portrait_checksum !== null
                ? substr($profile->portrait_checksum, 0, 16)
                : '';
        });

        return $version !== '' ? $version : null;
    }
}
