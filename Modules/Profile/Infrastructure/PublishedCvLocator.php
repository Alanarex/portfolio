<?php

declare(strict_types=1);

namespace Modules\Profile\Infrastructure;

use Modules\Profile\Contracts\PublicCvReader;
use Modules\Profile\Models\CvVersion;
use Modules\Settings\Contracts\PublicSettingsReader;

final class PublishedCvLocator implements PublicCvReader
{
    public function __construct(
        private readonly PublicSettingsReader $settings,
        private readonly CvStorage $storage,
    ) {}

    public function available(string $locale): bool
    {
        return $this->find($locale) !== null;
    }

    public function find(string $locale): ?CvVersion
    {
        $settings = $this->settings->forLocale($locale);
        if (($settings?->featureFlags['cv'] ?? false) !== true) {
            return null;
        }

        $version = CvVersion::query()
            ->where('locale', $locale)
            ->where('is_verified', true)
            ->whereNotNull('published_at')
            ->whereNull('archived_at')
            ->whereHas('profile', fn ($query) => $query->where('is_published', true))
            ->latest('published_at')
            ->latest('id')
            ->first();

        if ($version === null || ! $this->storage->isIntact($version->disk, $version->path, $version->checksum_sha256)) {
            return null;
        }

        return $version;
    }
}
