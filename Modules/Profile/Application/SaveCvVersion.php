<?php

declare(strict_types=1);

namespace Modules\Profile\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Profile\Models\CvVersion;
use Modules\Profile\Models\Profile;

final class SaveCvVersion
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data, User $actor, ?string $requestId, ?CvVersion $cvVersion = null): CvVersion
    {
        return DB::transaction(function () use ($data, $actor, $requestId, $cvVersion): CvVersion {
            $profile = Profile::query()->where('key', 'main')->lockForUpdate()->firstOrFail();
            $cvVersion ??= new CvVersion(['profile_id' => $profile->getKey()]);
            $created = ! $cvVersion->exists;
            $cvVersion->fill([
                'locale' => $data['locale'],
                'label' => $data['label'],
                'version_label' => $data['version_label'],
                'original_filename' => $data['original_filename'] ?? null,
                'mime_type' => $data['mime_type'] ?? null,
                'size_bytes' => $data['size_bytes'] ?? null,
                'checksum_sha256' => $data['checksum_sha256'] ?? null,
                'is_verified' => $data['is_verified'],
                'published_at' => $data['published'] ? ($cvVersion->published_at ?? now()) : null,
                'archived_at' => $data['archived'] ? ($cvVersion->archived_at ?? now()) : null,
            ]);
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

            return $cvVersion;
        });
    }
}
