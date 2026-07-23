<?php

declare(strict_types=1);

namespace Modules\Profile\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Profile\Database\Factories\CvVersionFactory;

/**
 * @property int $id
 * @property int $profile_id
 * @property string $locale
 * @property string $label
 * @property string $version_label
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int|null $size_bytes
 * @property string|null $checksum_sha256
 * @property bool $is_verified
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $archived_at
 */
#[Fillable(['profile_id', 'locale', 'label', 'version_label', 'original_filename', 'mime_type', 'size_bytes', 'checksum_sha256', 'is_verified', 'published_at', 'archived_at'])]
final class CvVersion extends Model
{
    /** @use HasFactory<CvVersionFactory> */
    use HasFactory;

    protected static function newFactory(): CvVersionFactory
    {
        return CvVersionFactory::new();
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'published_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
        ];
    }
}
