<?php

declare(strict_types=1);

namespace Modules\Projects\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Projects\Contracts\MediaStorage;
use Modules\Projects\Infrastructure\ProjectsCache;
use Modules\Projects\Models\Project;

final class DeleteProject
{
    public function __construct(
        private readonly AuditRecorder $auditRecorder,
        private readonly MediaStorage $mediaStorage,
    ) {}

    public function execute(Project $project, User $actor, ?string $requestId): void
    {
        $project = DB::transaction(function () use ($project, $actor, $requestId): Project {
            $project = Project::query()->lockForUpdate()->findOrFail($project->getKey());
            if ($project->deletion_pending_at === null) {
                $project->deletion_pending_at = now();
                $project->save();
                $project->mediaAssets()->update(['deletion_pending_at' => now()]);
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'project.deletion-requested',
                    subjectType: 'project',
                    subjectId: $project->uuid,
                    changedFields: ['deletion_pending_at'],
                    requestId: $requestId,
                );
            }

            return $project;
        });
        ProjectsCache::invalidate();

        $files = $project->mediaAssets()->get(['disk', 'path'])
            ->map(fn ($asset): array => ['disk' => $asset->disk, 'path' => $asset->path])
            ->all();
        foreach ($files as $file) {
            $this->mediaStorage->delete($file['disk'], $file['path']);
        }

        DB::transaction(function () use ($project, $actor, $requestId): void {
            $pending = Project::query()->lockForUpdate()->findOrFail($project->getKey());
            $pending->delete();
            $this->auditRecorder->record(
                actor: $actor,
                action: 'project.deleted',
                subjectType: 'project',
                subjectId: $project->uuid,
                changedFields: [],
                requestId: $requestId,
            );
        });

        ProjectsCache::invalidate();
    }
}
