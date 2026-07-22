<?php

declare(strict_types=1);

namespace Modules\Profile\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Profile\Contracts\PublicProfileReader;
use Modules\Profile\Data\PublicProfileData;
use Modules\Profile\Models\Profile;
use Modules\Profile\Models\ProfileTranslation;

final class DatabasePublicProfileReader implements PublicProfileReader
{
    public function forLocale(string $locale): ?PublicProfileData
    {
        if (! in_array($locale, ['fr', 'en'], true)) {
            return null;
        }

        return Cache::remember(ProfileCache::key($locale), now()->addMinutes(5), function () use ($locale): ?PublicProfileData {
            $profile = Profile::query()
                ->where('key', 'main')
                ->where('is_published', true)
                ->with('translations')
                ->first();

            if ($profile === null) {
                return null;
            }

            $translation = $profile->translations->firstWhere('locale', $locale)
                ?? $profile->translations->firstWhere('locale', 'fr');

            if (! $translation instanceof ProfileTranslation) {
                return null;
            }

            /** @var list<string> $titles */
            $titles = $translation->professional_titles;

            return new PublicProfileData(
                displayName: $profile->display_name,
                professionalTitles: $titles,
                summary: $translation->summary,
                biography: $translation->biography,
                location: $profile->show_location ? $translation->location_label : null,
                availability: $profile->show_availability ? $translation->availability : null,
            );
        });
    }
}
