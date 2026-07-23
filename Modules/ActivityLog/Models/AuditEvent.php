<?php

declare(strict_types=1);

namespace Modules\ActivityLog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $actor_user_id
 * @property string $action
 * @property string $subject_type
 * @property string $subject_id
 * @property list<string> $changed_fields
 * @property string|null $request_id
 */
#[Fillable(['actor_user_id', 'action', 'subject_type', 'subject_id', 'changed_fields', 'request_id'])]
final class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'changed_fields' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }
}
