<?php

declare(strict_types=1);

namespace Modules\Skills\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Skills\Database\Factories\SkillCategoryFactory;
use Modules\Skills\Enums\PublicationStatus;

/**
 * @property int $id
 * @property string $key
 * @property PublicationStatus $status
 * @property bool $is_visible
 * @property int $sort_order
 * @property-read Collection<int, SkillCategoryTranslation> $translations
 * @property-read Collection<int, Skill> $skills
 */
#[Fillable(['key', 'status', 'is_visible', 'sort_order', 'published_at'])]
final class SkillCategory extends Model
{
    /** @use HasFactory<SkillCategoryFactory> */
    use HasFactory;

    protected static function newFactory(): SkillCategoryFactory
    {
        return SkillCategoryFactory::new();
    }

    /** @return HasMany<SkillCategoryTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(SkillCategoryTranslation::class);
    }

    /** @return HasMany<Skill, $this> */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'is_visible' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
