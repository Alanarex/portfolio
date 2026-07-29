<?php

declare(strict_types=1);

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Projects\Database\Factories\ProjectFactory;
use Modules\Projects\Enums\ProjectLifecycle;
use Modules\Projects\Enums\PublicationStatus;
use Modules\Projects\Enums\RepositoryVisibility;

/**
 * @property int $id
 * @property string $uuid
 * @property string $slug
 * @property ProjectLifecycle $lifecycle_status
 * @property list<string> $technologies
 * @property int|null $start_year
 * @property int|null $start_month
 * @property int|null $end_year
 * @property int|null $end_month
 * @property bool $is_ongoing
 * @property RepositoryVisibility $repository_visibility
 * @property string|null $repository_url
 * @property bool $show_repository
 * @property string|null $demo_url
 * @property bool $show_demo
 * @property PublicationStatus $publication_status
 * @property bool $is_featured
 * @property int|null $featured_order
 * @property int $sort_order
 * @property string|null $source_reference
 * @property \Carbon\CarbonImmutable|null $deletion_pending_at
 * @property-read Collection<int, ProjectTranslation> $translations
 * @property-read Collection<int, CaseStudySection> $caseStudySections
 * @property-read Collection<int, MediaAsset> $mediaAssets
 */
#[Fillable([
    'uuid', 'slug', 'lifecycle_status', 'technologies', 'start_year', 'start_month', 'end_year',
    'end_month', 'is_ongoing', 'repository_visibility', 'repository_url', 'show_repository',
    'demo_url', 'show_demo', 'publication_status', 'is_featured', 'featured_order', 'sort_order',
    'source_reference', 'published_at',
    'deletion_pending_at',
])]
final class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    /** @return HasMany<ProjectTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    /** @return HasMany<CaseStudySection, $this> */
    public function caseStudySections(): HasMany
    {
        return $this->hasMany(CaseStudySection::class);
    }

    /** @return HasMany<MediaAsset, $this> */
    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'lifecycle_status' => ProjectLifecycle::class,
            'technologies' => 'array',
            'is_ongoing' => 'boolean',
            'repository_visibility' => RepositoryVisibility::class,
            'repository_url' => 'encrypted',
            'show_repository' => 'boolean',
            'show_demo' => 'boolean',
            'publication_status' => PublicationStatus::class,
            'is_featured' => 'boolean',
            'published_at' => 'immutable_datetime',
            'deletion_pending_at' => 'immutable_datetime',
        ];
    }
}
