<?php

declare(strict_types=1);

namespace Modules\Skills\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Skills\Database\Factories\SkillFactory;
use Modules\Skills\Enums\PublicationStatus;

/**
 * @property int $id
 * @property int $skill_category_id
 * @property string $key
 * @property PublicationStatus $status
 * @property bool $is_visible
 * @property int $sort_order
 * @property-read Collection<int, SkillTranslation> $translations
 */
#[Fillable(['skill_category_id', 'key', 'status', 'is_visible', 'sort_order', 'published_at'])]
final class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    protected static function newFactory(): SkillFactory
    {
        return SkillFactory::new();
    }

    /** @return HasMany<SkillTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(SkillTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'is_visible' => 'boolean', 'published_at' => 'immutable_datetime'];
    }
}
