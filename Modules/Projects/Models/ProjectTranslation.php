<?php

declare(strict_types=1);

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $project_id
 * @property string $locale
 * @property string $title
 * @property string $summary
 * @property string $role
 * @property string|null $seo_title
 * @property string|null $seo_description
 */
#[Fillable(['project_id', 'locale', 'title', 'summary', 'role', 'seo_title', 'seo_description'])]
final class ProjectTranslation extends Model {}
