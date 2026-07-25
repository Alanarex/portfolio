<?php

declare(strict_types=1);

namespace Modules\Career\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $certification_id
 * @property string $locale
 * @property string $name
 * @property string|null $skill_label
 */
#[Fillable(['certification_id', 'locale', 'name', 'skill_label'])]
final class CertificationTranslation extends Model {}
