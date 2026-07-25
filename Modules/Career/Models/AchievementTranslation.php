<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $achievement_id
 * @property string $locale
 * @property string $statement
 */
#[Fillable(['achievement_id', 'locale', 'statement'])]
final class AchievementTranslation extends Model
{
    protected $table = 'career_achievement_translations';
}
