<?php

declare(strict_types=1);

namespace Modules\Settings\Infrastructure;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Contracts\PublicSettingsReader;
use Modules\Settings\Data\PublicSettingsData;
use Modules\Settings\Models\Locale;
use Modules\Settings\Models\SiteSetting;

final class DatabasePublicSettingsReader implements PublicSettingsReader
{
    public function forLocale(string $locale): ?PublicSettingsData
    {
        $isActive = Locale::query()->whereKey($locale)->where('status', 'active')->exists();
        if (! $isActive) {
            return null;
        }

        return Cache::remember(SettingsCache::key($locale), now()->addMinutes(5), function () use ($locale): ?PublicSettingsData {
            $settings = SiteSetting::query()
                ->where('key', 'main')
                ->with(['socialLinks', 'featureFlags'])
                ->first();

            if ($settings === null) {
                return null;
            }

            /** @var array<string, string> $socialLinks */
            $socialLinks = $settings->socialLinks
                ->filter(fn ($link): bool => $link->is_enabled && $link->is_public)
                ->mapWithKeys(fn ($link): array => [$link->platform => $link->url])
                ->all();
            /** @var array<string, bool> $featureFlags */
            $featureFlags = $settings->featureFlags
                ->mapWithKeys(fn ($flag): array => [$flag->key => (bool) $flag->enabled])
                ->all();

            return new PublicSettingsData(
                siteName: $settings->site_name,
                locale: $locale,
                email: $settings->show_email ? $settings->contact_email : null,
                phone: $settings->show_phone ? $settings->contact_phone : null,
                socialLinks: $socialLinks,
                featureFlags: $featureFlags,
            );
        });
    }
}
