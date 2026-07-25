<?php

declare(strict_types=1);

namespace Modules\Skills\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $skill_category_id
 * @property string $locale
 * @property string $name
 */
#[Fillable(['skill_category_id', 'locale', 'name'])]
final class SkillCategoryTranslation extends Model {}
