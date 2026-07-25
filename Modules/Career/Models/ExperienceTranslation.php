<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $experience_id
 * @property string $locale
 * @property string $role
 * @property string|null $employment_type
 * @property string|null $location
 * @property string|null $summary
 * @property list<string> $highlights
 */
#[Fillable(['experience_id', 'locale', 'role', 'employment_type', 'location', 'summary', 'highlights'])]
final class ExperienceTranslation extends Model
{
    protected $table = 'career_experience_translations';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['highlights' => 'array'];
    }
}
