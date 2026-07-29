<?php

declare(strict_types=1);

namespace Modules\Projects\Application;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\Projects\Infrastructure\ProjectsCache;
use Modules\Projects\Models\Project;

final class ReorderProjects
{
    public function __construct(private readonly AuditRecorder $auditRecorder) {}

    /** @param list<int> $ids */
    public function execute(array $ids, User $actor, ?string $requestId): void
    {
        $changed = DB::transaction(function () use ($ids, $actor, $requestId): bool {
            $projects = Project::query()->whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');
            $changed = false;
            foreach ($ids as $index => $id) {
                $project = $projects->get($id);
                abort_unless($project !== null, 422);
                $order = ($index + 1) * 10;
                if ($project->sort_order !== $order) {
                    $project->sort_order = $order;
                    $project->save();
                    $changed = true;
                }
            }

            if ($changed) {
                $this->auditRecorder->record(
                    actor: $actor,
                    action: 'projects.reordered',
                    subjectType: 'projects',
                    subjectId: 'portfolio',
                    changedFields: ['sort_order'],
                    requestId: $requestId,
                );
            }

            return $changed;
        });

        if ($changed) {
            ProjectsCache::invalidate();
        }
    }
}
