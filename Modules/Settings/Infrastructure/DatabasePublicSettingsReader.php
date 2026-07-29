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

        /** @var array{site_name: string, locale: string, email: string|null, phone: string|null, social_links: array<string, string>, feature_flags: array<string, bool>, contact_form_enabled: bool}|null $payload */
        $payload = Cache::remember(SettingsCache::key($locale), now()->addMinutes(5), function () use ($locale): ?array {
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

            return [
                'site_name' => $settings->site_name,
                'locale' => $locale,
                'email' => $settings->show_email ? $settings->contact_email : null,
                'phone' => $settings->show_phone ? $settings->contact_phone : null,
                'social_links' => $socialLinks,
                'feature_flags' => $featureFlags,
                'contact_form_enabled' => ($featureFlags['contact'] ?? false) && filled($settings->contact_email),
            ];
        });

        return $payload === null ? null : new PublicSettingsData(
            siteName: $payload['site_name'],
            locale: $payload['locale'],
            email: $payload['email'],
            phone: $payload['phone'],
            socialLinks: $payload['social_links'],
            featureFlags: $payload['feature_flags'],
            contactFormEnabled: $payload['contact_form_enabled'],
        );
    }
}
