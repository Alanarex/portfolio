<?php

declare(strict_types=1);

namespace Modules\ActivityLog\Infrastructure;

use App\Models\User;
use Modules\ActivityLog\Contracts\AuditRecorder;
use Modules\ActivityLog\Models\AuditEvent;

final class DatabaseAuditRecorder implements AuditRecorder
{
    public function record(
        ?User $actor,
        string $action,
        string $subjectType,
        string $subjectId,
        array $changedFields,
        ?string $requestId = null,
    ): void {
        AuditEvent::query()->create([
            'actor_user_id' => $actor?->getKey(),
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'changed_fields' => array_values(array_unique($changedFields)),
            'request_id' => $requestId,
        ]);
    }
}
