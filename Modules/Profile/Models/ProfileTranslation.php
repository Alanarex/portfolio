<?php

declare(strict_types=1);

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Profile\Database\Factories\ProfileTranslationFactory;

/**
 * @property int $id
 * @property int $profile_id
 * @property string $locale
 * @property list<string> $professional_titles
 * @property string $summary
 * @property string|null $location_label
 * @property string|null $availability
 * @property string $biography
 */
#[Fillable(['profile_id', 'locale', 'professional_titles', 'summary', 'location_label', 'availability', 'biography'])]
final class ProfileTranslation extends Model
{
    /** @use HasFactory<ProfileTranslationFactory> */
    use HasFactory;

    protected static function newFactory(): ProfileTranslationFactory
    {
        return ProfileTranslationFactory::new();
    }

    /** @return BelongsTo<Profile, $this> */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['professional_titles' => 'array'];
    }
}
