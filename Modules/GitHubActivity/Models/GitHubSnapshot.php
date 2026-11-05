<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $username
 * @property list<array<string, mixed>> $repositories
 * @property list<array<string, mixed>> $events
 * @property Carbon $synced_at
 */
#[Fillable(['username', 'repositories', 'events', 'synced_at'])]
final class GitHubSnapshot extends Model
{
    protected $table = 'github_activity_snapshots';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'repositories' => 'array',
            'events' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
