<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $language_proficiency_id
 * @property string $locale
 * @property string $name
 * @property string $proficiency_label
 * @property string|null $evidence
 */
#[Fillable(['language_proficiency_id', 'locale', 'name', 'proficiency_label', 'evidence'])]
final class LanguageProficiencyTranslation extends Model {}
