<?php

declare(strict_types=1);

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $case_study_section_id
 * @property string $locale
 * @property string $heading
 * @property string $body
 */
#[Fillable(['case_study_section_id', 'locale', 'heading', 'body'])]
final class CaseStudySectionTranslation extends Model {}
