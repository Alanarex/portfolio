<?php

declare(strict_types=1);

namespace Modules\Projects\Application;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Projects\Contracts\MediaStorage;
use Modules\Projects\Data\StoredMediaData;
use Modules\Projects\Enums\MediaKind;
use Modules\Projects\Infrastructure\ProjectsCache;
use Modules\Projects\Models\MediaAsset;
use Modules\Projects\Models\Project;
use Throwable;

final class StoreMedia
{
    public function __construct(
        private readonly AuditRecorder $auditRecorder,
        private readonly MediaStorage $mediaStorage,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, UploadedFile $file, User $actor, ?string $requestId): MediaAsset
    {
        $uuid = (string) Str::uuid();
        $kind = MediaKind::from($data['kind']);
        /** @var StoredMediaData|null $stored */
        $stored = null;

        try {
            $asset = DB::transaction(function () use (
                $data,
                $file,
                $actor,
                $requestId,
                $uuid,
                $kind,
                &$stored,
            ): MediaAsset {
                $project = isset($data['project_id'])
                    ? Project::query()->lockForUpdate()->findOrFail((int) $data['project_id'])
                    : null;
                if ($project?->deletion_pending_at !== null) {
                    throw ValidationException::withMessages([
                        'project_id' => 'Ce projet est en cours de suppression et ne peut plus recevoir de média.',
                    ]);
                }

                $stored = $this->mediaStorage->store(
                    $file,
                    $project?->uuid ?? 'shared',
                    $kind,
                    $uuid,
                );
                $asset = MediaAsset::query()->create([
                    'uuid' => $uuid,
                    'project_id' => $project?->getKey(),
                    'kind' => $kind,
                    'disk' => $stored->disk,
                    'path' => $stored->path,
                    'original_filename' => $stored->originalFilename,
                    'mime_type' => $stored->mimeType,
                    'extension' => $stored->extension,
                    'size_bytes' => $stored->sizeBytes,
                    'checksum_sha256' => $stored->checksumSha256,
                    'width' => $stored->width,
                    'height' => $stored->height,
                    'is_public' => $data['is_public'],
                    'sort_order' => $data['sort_order'],
                ]);
                $this->syncTranslations($asset, $data['translations']);
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'media.uploaded',
                    subjectType: 'media-asset',
                    subjectId: $asset->uuid,
                    changedFields: ['file', 'project_id', 'kind', 'translations', 'is_public', 'sort_order'],
                    requestId: $requestId,
                );

                return $asset;
            });
        } catch (Throwable $throwable) {
            if ($stored !== null) {
                $this->mediaStorage->delete($stored->disk, $stored->path);
            }
            throw $throwable;
        }

        ProjectsCache::invalidate();

        return $asset;
    }

    /** @param array<string, array<string, mixed>> $translations */
    private function syncTranslations(MediaAsset $asset, array $translations): void
    {
        foreach (['fr', 'en'] as $locale) {
            $content = $translations[$locale];
            if (! filled($content['alt_text'] ?? null) && ! filled($content['caption'] ?? null)) {
                continue;
            }
            $asset->translations()->create([
                'locale' => $locale,
                'alt_text' => $content['alt_text'] ?: null,
                'caption' => $content['caption'] ?: null,
            ]);
        }
    }
}
