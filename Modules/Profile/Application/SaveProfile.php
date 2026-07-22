<?php

declare(strict_types=1);

namespace Modules\Profile\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Profile\Infrastructure\ProfileCache;
use Modules\Profile\Models\Profile;
use Modules\Profile\Models\ProfileTranslation;

final class SaveProfile
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId): Profile
    {
        $changedFields = [];
        $created = false;

        $profile = DB::transaction(function () use ($data, $actor, $requestId, &$changedFields, &$created): Profile {
            $profile = Profile::query()->where('key', 'main')->lockForUpdate()->first() ?? new Profile(['key' => 'main']);
            $created = ! $profile->exists;
            $wasPublished = $profile->is_published === true;
            $willPublish = (bool) $data['is_published'];

            $profile->fill([
                'display_name' => $data['display_name'],
                'is_published' => $willPublish,
                'show_location' => $data['show_location'],
                'show_availability' => $data['show_availability'],
                'published_at' => $willPublish ? ($wasPublished ? $profile->published_at : now()) : null,
            ]);
            $changedFields = array_keys($profile->getDirty());
            $profile->save();

            /** @var array<string, array<string, mixed>|null> $translations */
            $translations = $data['translations'];
            foreach (['fr', 'en', 'ar'] as $locale) {
                $translationData = $translations[$locale] ?? null;
                $hasContent = is_array($translationData)
                    && (
                        $this->hasProfessionalTitle($translationData['professional_titles'] ?? null)
                        || filled($translationData['summary'] ?? null)
                        || filled($translationData['biography'] ?? null)
                    );

                $translation = ProfileTranslation::query()
                    ->where('profile_id', $profile->getKey())
                    ->where('locale', $locale)
                    ->first();

                if (! $hasContent) {
                    if ($locale === 'ar' && $translation !== null) {
                        $translation->delete();
                        $changedFields[] = 'translations.ar';
                    }

                    continue;
                }

                $translation ??= new ProfileTranslation([
                    'profile_id' => $profile->getKey(),
                    'locale' => $locale,
                ]);
                $translation->fill([
                    'professional_titles' => array_values($translationData['professional_titles'] ?? []),
                    'summary' => (string) ($translationData['summary'] ?? ''),
                    'location_label' => $translationData['location_label'] ?? null,
                    'availability' => $translationData['availability'] ?? null,
                    'biography' => (string) ($translationData['biography'] ?? ''),
                ]);

                foreach (array_keys($translation->getDirty()) as $field) {
                    if (! in_array($field, ['profile_id', 'locale'], true)) {
                        $changedFields[] = "translations.{$locale}.{$field}";
                    }
                }
                $translation->save();
            }

            $changedFields = array_values(array_unique(array_diff($changedFields, ['updated_at'])));
            if ($changedFields !== []) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: $created ? 'profile.created' : 'profile.updated',
                    subjectType: 'profile',
                    subjectId: 'main',
                    changedFields: $changedFields,
                    requestId: $requestId,
                );
            }

            return $profile->load(['translations', 'cvVersions']);
        });

        if ($changedFields !== []) {
            ProfileCache::invalidate();
        }

        return $profile;
    }

    private function hasProfessionalTitle(mixed $titles): bool
    {
        if (! is_array($titles)) {
            return false;
        }

        foreach ($titles as $title) {
            if (is_string($title) && trim($title) !== '') {
                return true;
            }
        }

        return false;
    }
}
