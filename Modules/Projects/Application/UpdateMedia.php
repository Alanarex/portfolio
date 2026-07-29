<?php

declare(strict_types=1);

namespace Modules\Projects\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Projects\Infrastructure\ProjectsCache;
use Modules\Projects\Models\MediaAsset;

final class UpdateMedia
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(MediaAsset $asset, array $data, User $actor, ?string $requestId): void
    {
        $changed = DB::transaction(function () use ($asset, $data, $actor, $requestId): bool {
            $asset = MediaAsset::query()->lockForUpdate()->findOrFail($asset->getKey());
            $asset->fill([
                'is_public' => $data['is_public'],
                'sort_order' => $data['sort_order'],
            ]);
            $changedFields = array_values(array_diff(array_keys($asset->getDirty()), ['updated_at']));
            $asset->save();
            $translationChanged = false;

            foreach (['fr', 'en'] as $locale) {
                $content = $data['translations'][$locale];
                $translation = $asset->translations()->where('locale', $locale)->first();
                if (! filled($content['alt_text'] ?? null) && ! filled($content['caption'] ?? null)) {
                    if ($translation !== null) {
                        $translation->delete();
                        $translationChanged = true;
                    }

                    continue;
                }
                $translation ??= $asset->translations()->make(['locale' => $locale]);
                $translation->fill([
                    'alt_text' => $content['alt_text'] ?: null,
                    'caption' => $content['caption'] ?: null,
                ]);
                $translationChanged = $translationChanged || ! $translation->exists || $translation->isDirty();
                $translation->save();
            }

            if ($translationChanged) {
                $changedFields[] = 'translations';
            }
            $changedFields = array_values(array_unique($changedFields));
            if ($changedFields !== []) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'media.updated',
                    subjectType: 'media-asset',
                    subjectId: $asset->uuid,
                    changedFields: $changedFields,
                    requestId: $requestId,
                );
            }

            return $changedFields !== [];
        });

        if ($changed) {
            ProjectsCache::invalidate();
        }
    }
}
