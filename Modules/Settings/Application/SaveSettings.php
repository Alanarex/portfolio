<?php

declare(strict_types=1);

namespace Modules\Settings\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Settings\Infrastructure\SettingsCache;
use Modules\Settings\Models\PublicFeatureFlag;
use Modules\Settings\Models\SiteSetting;
use Modules\Settings\Models\SocialLink;

final class SaveSettings
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId): SiteSetting
    {
        $changedFields = [];
        $created = false;

        $settings = DB::transaction(function () use ($data, $actor, $requestId, &$changedFields, &$created): SiteSetting {
            $settings = SiteSetting::query()->where('key', 'main')->lockForUpdate()->first() ?? new SiteSetting(['key' => 'main']);
            $created = ! $settings->exists;
            $settings->fill([
                'site_name' => $data['site_name'],
                'default_locale' => 'fr',
                'show_email' => $data['show_email'],
                'show_phone' => $data['show_phone'],
            ]);
            if ($settings->contact_email !== ($data['contact_email'] ?? null)) {
                $settings->contact_email = $data['contact_email'] ?? null;
            }
            if ($settings->contact_phone !== ($data['contact_phone'] ?? null)) {
                $settings->contact_phone = $data['contact_phone'] ?? null;
            }
            $changedFields = array_keys($settings->getDirty());
            $settings->save();

            /** @var list<array<string, mixed>> $linkData */
            $linkData = $data['social_links'];
            $submittedPlatforms = [];
            foreach ($linkData as $linkAttributes) {
                $platform = (string) $linkAttributes['platform'];
                $submittedPlatforms[] = $platform;
                $link = SocialLink::query()->firstOrNew([
                    'site_setting_id' => $settings->getKey(),
                    'platform' => $platform,
                ]);
                if ($link->url !== $linkAttributes['url']) {
                    $link->url = $linkAttributes['url'];
                }
                $link->fill([
                    'is_enabled' => $linkAttributes['is_enabled'],
                    'is_public' => $linkAttributes['is_public'],
                    'sort_order' => $linkAttributes['sort_order'],
                ]);
                if ($link->isDirty()) {
                    $changedFields[] = "social_links.{$platform}";
                    $link->save();
                }
            }

            $removedPlatforms = SocialLink::query()
                ->where('site_setting_id', $settings->getKey())
                ->when($submittedPlatforms !== [], fn ($query) => $query->whereNotIn('platform', $submittedPlatforms))
                ->pluck('platform');
            if ($submittedPlatforms === []) {
                $removedPlatforms = SocialLink::query()->where('site_setting_id', $settings->getKey())->pluck('platform');
            }
            SocialLink::query()
                ->where('site_setting_id', $settings->getKey())
                ->when($submittedPlatforms !== [], fn ($query) => $query->whereNotIn('platform', $submittedPlatforms))
                ->delete();
            foreach ($removedPlatforms as $platform) {
                $changedFields[] = "social_links.{$platform}";
            }

            /** @var array<string, bool> $flags */
            $flags = $data['feature_flags'];
            foreach ($flags as $key => $enabled) {
                $flag = PublicFeatureFlag::query()->firstOrNew([
                    'site_setting_id' => $settings->getKey(),
                    'key' => $key,
                ]);
                $flag->enabled = $enabled;
                if ($flag->isDirty()) {
                    $changedFields[] = "feature_flags.{$key}";
                    $flag->save();
                }
            }

            $changedFields = array_values(array_unique(array_diff($changedFields, ['updated_at'])));
            if ($changedFields !== []) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: $created ? 'settings.created' : 'settings.updated',
                    subjectType: 'site-settings',
                    subjectId: 'main',
                    changedFields: $changedFields,
                    requestId: $requestId,
                );
            }

            return $settings->load(['socialLinks', 'featureFlags']);
        });

        if ($changedFields !== []) {
            SettingsCache::invalidate();
        }

        return $settings;
    }
}
