<?php

declare(strict_types=1);

namespace Modules\Projects\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Projects\Contracts\MediaStorage;
use Modules\Projects\Infrastructure\ProjectsCache;
use Modules\Projects\Models\MediaAsset;

final class DeleteMedia
{
    public function __construct(
        private readonly AuditRecorder $auditRecorder,
        private readonly MediaStorage $mediaStorage,
    ) {}

    public function execute(MediaAsset $asset, User $actor, ?string $requestId): void
    {
        $asset = DB::transaction(function () use ($asset, $actor, $requestId): MediaAsset {
            $asset = MediaAsset::query()->lockForUpdate()->findOrFail($asset->getKey());
            if ($asset->deletion_pending_at === null) {
                $asset->deletion_pending_at = now();
                $asset->save();
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'media.deletion-requested',
                    subjectType: 'media-asset',
                    subjectId: $asset->uuid,
                    changedFields: ['deletion_pending_at'],
                    requestId: $requestId,
                );
            }

            return $asset;
        });
        ProjectsCache::invalidate();

        $this->mediaStorage->delete($asset->disk, $asset->path);

        DB::transaction(function () use ($asset, $actor, $requestId): void {
            $pending = MediaAsset::query()->lockForUpdate()->findOrFail($asset->getKey());
            $pending->delete();
            $this->auditRecorder->record(
                actor: $actor,
                action: 'media.deleted',
                subjectType: 'media-asset',
                subjectId: $asset->uuid,
                changedFields: [],
                requestId: $requestId,
            );
        });

        ProjectsCache::invalidate();
    }
}
