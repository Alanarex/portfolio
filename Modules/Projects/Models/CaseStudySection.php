<?php

declare(strict_types=1);

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Projects\Enums\CaseStudySectionType;

/**
 * @property int $id
 * @property int $project_id
 * @property CaseStudySectionType $type
 * @property bool $is_public
 * @property bool $is_verified
 * @property int $sort_order
 * @property-read Project $project
 * @property-read Collection<int, CaseStudySectionTranslation> $translations
 */
#[Fillable(['project_id', 'type', 'is_public', 'is_verified', 'sort_order'])]
final class CaseStudySection extends Model
{
    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<CaseStudySectionTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(CaseStudySectionTranslation::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'type' => CaseStudySectionType::class,
            'is_public' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }
}
