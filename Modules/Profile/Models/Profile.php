<?php

declare(strict_types=1);

namespace Modules\Profile\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Profile\Database\Factories\ProfileFactory;

/**
 * @property int $id
 * @property string $key
 * @property string $display_name
 * @property bool $is_published
 * @property bool $show_location
 * @property bool $show_availability
 * @property CarbonImmutable|null $published_at
 * @property-read Collection<int, ProfileTranslation> $translations
 * @property-read Collection<int, CvVersion> $cvVersions
 */
#[Fillable(['key', 'display_name', 'is_published', 'show_location', 'show_availability', 'published_at'])]
final class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory;

    protected static function newFactory(): ProfileFactory
    {
        return ProfileFactory::new();
    }

    /** @return HasMany<ProfileTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ProfileTranslation::class);
    }

    /** @return HasMany<CvVersion, $this> */
    public function cvVersions(): HasMany
    {
        return $this->hasMany(CvVersion::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_location' => 'boolean',
            'show_availability' => 'boolean',
            'published_at' => 'immutable_datetime',
        ];
    }
}
