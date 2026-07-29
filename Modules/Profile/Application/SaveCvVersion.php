<?php

declare(strict_types=1);

namespace Modules\Profile\Application;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Profile\Infrastructure\CvStorage;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;
use Throwable;

final class SaveCvVersion
{
    public function __construct(
        private readonly AuditRecorder $auditRecorder,
        private readonly CvStorage $storage,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId, ?CvVersion $cvVersion = null): CvVersion
    {
        $uploaded = $data['document'] ?? null;
        $stored = $uploaded instanceof UploadedFile ? $this->storage->store($uploaded) : null;
        $cvVersionId = $cvVersion?->getKey();

        try {
            $result = DB::transaction(function () use ($data, $actor, $requestId, $cvVersionId, $stored): array {
                $profile = Profile::query()->where('key', 'main')->lockForUpdate()->firstOrFail();
                $cvVersion = $cvVersionId === null
                    ? new CvVersion(['profile_id' => $profile->getKey()])
                    : CvVersion::query()
                        ->where('profile_id', $profile->getKey())
                        ->whereKey($cvVersionId)
                        ->lockForUpdate()
                        ->firstOrFail();
                $created = ! $cvVersion->exists;
                $oldDisk = $cvVersion->disk;
                $oldPath = $cvVersion->path;
                $cvVersion->fill([
                    'locale' => $data['locale'],
                    'label' => $data['label'],
                    'version_label' => $data['version_label'],
                    'is_verified' => $data['is_verified'],
                    'published_at' => $data['published'] ? ($cvVersion->published_at ?? now()) : null,
                    'archived_at' => $data['archived'] ? ($cvVersion->archived_at ?? now()) : null,
                    ...($stored ?? []),
                ]);

                if ($data['published']) {
                    $previouslyPublished = CvVersion::query()
                        ->where('profile_id', $profile->getKey())
                        ->where('locale', $data['locale'])
                        ->when($cvVersion->exists, fn ($query) => $query->whereKeyNot($cvVersion->getKey()))
                        ->whereNotNull('published_at')
                        ->lockForUpdate()
                        ->get();

                    foreach ($previouslyPublished as $previousVersion) {
                        $previousVersion->update(['published_at' => null]);
                        $this->auditRecorder->record(
                            actor: $actor,
                            action: 'cv-version.unpublished',
                            subjectType: 'cv-version',
                            subjectId: (string) $previousVersion->getKey(),
                            changedFields: ['published_at'],
                            requestId: $requestId,
                        );
                    }
                }

                $changedFields = array_values(array_diff(array_keys($cvVersion->getDirty()), ['updated_at']));
                $cvVersion->save();

                if ($changedFields !== []) {
                    $this->auditRecorder->record(
                        actor: $actor,
                        action: $created ? 'cv-version.created' : 'cv-version.updated',
                        subjectType: 'cv-version',
                        subjectId: (string) $cvVersion->getKey(),
                        changedFields: $changedFields,
                        requestId: $requestId,
                    );
                }

                return [
                    'version' => $cvVersion,
                    'old_disk' => $oldDisk,
                    'old_path' => $oldPath,
                ];
            });
        } catch (Throwable $exception) {
            if ($stored !== null) {
                $this->storage->delete($stored['disk'], $stored['path']);
            }

            throw $exception;
        }

        if ($stored !== null && $result['old_path'] !== null && $result['old_path'] !== $stored['path']) {
            $this->storage->delete($result['old_disk'], $result['old_path']);
        }

        return $result['version'];
    }
}
