<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $education_record_id
 * @property string $locale
 * @property string $program
 * @property string|null $level
 * @property string|null $location
 * @property string|null $summary
 */
#[Fillable(['education_record_id', 'locale', 'program', 'level', 'location', 'summary'])]
final class EducationRecordTranslation extends Model {}
