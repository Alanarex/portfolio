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

        /** @var array{display_name: string, professional_titles: list<string>, summary: string, biography: string, location: string|null, availability: string|null}|null $payload */
        $payload = Cache::remember(ProfileCache::key($locale), now()->addMinutes(5), function () use ($locale): ?array {
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

            return [
                'display_name' => $profile->display_name,
                'professional_titles' => $titles,
                'summary' => $translation->summary,
                'biography' => $translation->biography,
                'location' => $profile->show_location ? $translation->location_label : null,
                'availability' => $profile->show_availability ? $translation->availability : null,
            ];
        });

        return $payload === null ? null : new PublicProfileData(
            displayName: $payload['display_name'],
            professionalTitles: $payload['professional_titles'],
            summary: $payload['summary'],
            biography: $payload['biography'],
            location: $payload['location'],
            availability: $payload['availability'],
        );
    }
}
