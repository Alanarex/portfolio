<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Career\Database\Factories\LanguageProficiencyFactory;
use Modules\Career\Enums\PublicationStatus;

/**
 * @property int $id
 * @property string $key
 * @property string $language_code
 * @property string $proficiency_kind
 * @property string|null $cefr_level
 * @property PublicationStatus $status
 * @property int $sort_order
 * @property-read Collection<int, LanguageProficiencyTranslation> $translations
 */
#[Fillable(['key', 'language_code', 'proficiency_kind', 'cefr_level', 'status', 'sort_order', 'published_at'])]
final class LanguageProficiency extends Model
{
    /** @use HasFactory<LanguageProficiencyFactory> */
    use HasFactory;

    protected static function newFactory(): LanguageProficiencyFactory
    {
        return LanguageProficiencyFactory::new();
    }

    /** @return HasMany<LanguageProficiencyTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(LanguageProficiencyTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => PublicationStatus::class, 'published_at' => 'immutable_datetime'];
    }
}
