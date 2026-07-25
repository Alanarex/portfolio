<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Career\Database\Factories\AchievementFactory;
use Modules\Career\Enums\PublicationStatus;

/**
 * @property int $id
 * @property int $experience_id
 * @property string $key
 * @property PublicationStatus $status
 * @property bool $is_quantified
 * @property bool $is_verified
 * @property string|null $source_reference
 * @property int $sort_order
 * @property-read Collection<int, AchievementTranslation> $translations
 */
#[Fillable(['experience_id', 'key', 'status', 'is_quantified', 'is_verified', 'source_reference', 'sort_order'])]
final class Achievement extends Model
{
    /** @use HasFactory<AchievementFactory> */
    use HasFactory;

    protected $table = 'career_achievements';

    protected static function newFactory(): AchievementFactory
    {
        return AchievementFactory::new();
    }

    /** @return HasMany<AchievementTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(AchievementTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'is_quantified' => 'boolean', 'is_verified' => 'boolean'];
    }
}
