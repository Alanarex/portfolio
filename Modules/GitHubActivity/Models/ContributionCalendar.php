<?php

declare(strict_types=1);

namespace Modules\GitHubActivity\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $source
 * @property string $username
 * @property array<string, int> $days
 * @property Carbon $synced_at
 */
#[Fillable(['source', 'username', 'days', 'synced_at'])]
final class ContributionCalendar extends Model
{
    protected $table = 'contribution_calendars';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'days' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
