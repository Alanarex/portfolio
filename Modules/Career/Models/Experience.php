<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Career\Database\Factories\ExperienceFactory;
use Modules\Career\Enums\PublicationStatus;

/**
 * @property int $id
 * @property string $key
 * @property string $organization
 * @property int $start_year
 * @property int|null $start_month
 * @property int|null $end_year
 * @property int|null $end_month
 * @property bool $is_current
 * @property PublicationStatus $status
 * @property int $sort_order
 * @property-read Collection<int, ExperienceTranslation> $translations
 * @property-read Collection<int, Achievement> $achievements
 */
#[Fillable(['key', 'organization', 'start_year', 'start_month', 'end_year', 'end_month', 'is_current', 'status', 'sort_order', 'published_at'])]
final class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    protected $table = 'career_experiences';

    protected static function newFactory(): ExperienceFactory
    {
        return ExperienceFactory::new();
    }

    /** @return HasMany<ExperienceTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ExperienceTranslation::class);
    }

    /** @return HasMany<Achievement, $this> */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_current' => 'boolean', 'status' => PublicationStatus::class, 'published_at' => 'immutable_datetime'];
    }
}
