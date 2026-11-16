<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property bool $succeeded
 * @property string|null $ip_masked
 * @property string|null $user_agent
 * @property CarbonImmutable $created_at
 */
#[Fillable(['user_id', 'succeeded', 'ip_masked', 'user_agent'])]
final class AdminLoginEvent extends Model
{
    public const UPDATED_AT = null;

    /** Nombre d'événements conservés. */
    public const KEEP = 50;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'succeeded' => 'boolean',
            'created_at' => 'immutable_datetime',
        ];
    }
}
