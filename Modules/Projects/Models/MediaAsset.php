<?php

declare(strict_types=1);

namespace Modules\Projects\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Projects\Enums\MediaKind;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $project_id
 * @property MediaKind $kind
 * @property string $disk
 * @property string $path
 * @property string $original_filename
 * @property string $mime_type
 * @property string $extension
 * @property int $size_bytes
 * @property string $checksum_sha256
 * @property int|null $width
 * @property int|null $height
 * @property bool $is_public
 * @property int $sort_order
 * @property CarbonImmutable|null $deletion_pending_at
 * @property-read Project|null $project
 * @property-read Collection<int, MediaAssetTranslation> $translations
 */
#[Fillable([
    'uuid', 'project_id', 'kind', 'disk', 'path', 'original_filename', 'mime_type', 'extension',
    'size_bytes', 'checksum_sha256', 'width', 'height', 'is_public', 'sort_order', 'deletion_pending_at',
])]
final class MediaAsset extends Model
{
    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<MediaAssetTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(MediaAssetTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'kind' => MediaKind::class,
            'path' => 'encrypted',
            'original_filename' => 'encrypted',
            'is_public' => 'boolean',
            'deletion_pending_at' => 'immutable_datetime',
        ];
    }
}
