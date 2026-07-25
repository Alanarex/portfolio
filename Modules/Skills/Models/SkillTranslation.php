<?php

declare(strict_types=1);

namespace Modules\Skills\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $skill_id
 * @property string $locale
 * @property string $name
 * @property string|null $description
 */
#[Fillable(['skill_id', 'locale', 'name', 'description'])]
final class SkillTranslation extends Model {}
