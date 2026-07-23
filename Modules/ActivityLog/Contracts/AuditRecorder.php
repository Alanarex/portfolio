<?php

declare(strict_types=1);

namespace Modules\ActivityLog\Contracts;

use App\Models\User;

interface AuditRecorder
{
    /** @param list<string> $changedFields */
    public function record(
        ?User $actor,
        string $action,
        string $subjectType,
        string $subjectId,
        array $changedFields,
        ?string $requestId = null,
    ): void;
}
